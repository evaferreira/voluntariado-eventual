<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\InscripcionesExport;
use App\Grupo;
use App\GrupoRolPersona;
use App\Inscripcion;
use App\Log;
use App\Mail\ActualizacionActividad;
use App\Persona;
use App\PuntoEncuentro;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Persona;
use Carbon\Carbon;
use App\Mail\MailConfimacionInscripcion;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Rap2hpoutre\FastExcel\FastExcel;

class InscripcionesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, Request $request)
    {
        $filtros = array_merge($request->all(), ['idActividad' => $id]);
        if($request->has('filter')){
            $filtros['HotFilter'] = $request->filter;
            unset($filtros['filter']);
        }
        if($request->has('condiciones'))
        {
            foreach ($request->condiciones as $condicion)
            {
                $condicion = json_decode($condicion, true);
                $filtros[$condicion['campo']] = [
                    'condicion' => $condicion['condicion'],
                    'valor' => $condicion['valor']
                ];
            }
            unset($filtros['condiciones']);
        }
        $export = new InscripcionesExport($filtros);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);

        //hack para solucionar problema con vuetable con checkboxes
        // https://github.com/ratiw/vuetable-2/issues/422
        $flattenCollection = $result->getCollection()->flatten();
        $result->setCollection($flattenCollection);

        return $result;

    }

    public function update(Request $request, $id, $inscripcion)
    {
        $inscripcion = Inscripcion::findOrFail($inscripcion);

        if($request->has('presente')){
            $inscripcion->presente = $request->presente;
        }

        if($request->has('pago')){
            $inscripcion->pago = $request->pago;
        }

        if (!empty($request->estado)){
            $inscripcion->estado = $request->estado;
        }
        if ($inscripcion->save()) {
            return response()->json('Ok', 200);
        }

        return response('Ocurrió un error al actualizar el estado', 500);
    }

    public function asignarRol(Request $request)
    {
        $idActividad = $request->actividad;
        foreach ($request->inscripciones as $idInscripcion)
        {
            $persona = Inscripcion::findOrFail($idInscripcion)->persona;
            if($grupoRol = $persona->grupoAsignadoEnActividad($idActividad))
            {
                $grupoRol->rol = $request->rol;
                $grupoRol->save();
            } else {
                //Nuevo
                $grupoRol = new GrupoRolPersona();
                $grupoRol->idPersona = $persona->idPersona;
                $grupoRol->idActividad = $idActividad;
                $grupoRol->idGrupo = Actividad::find($idActividad)->grupos()->raiz()->idGrupo;
                $grupoRol->rol = $request->rol;
                $grupoRol->save();
            }
        }
        return response()
            ->json("Rol " . $request->rol . " configurado a " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function asignarGrupo(Request $request)
    {
        $datos = $request->all();
        $idActividad = $request->actividad;
        foreach ($request->inscripciones as $idInscripcion)
        {
            $persona = Inscripcion::findOrFail($idInscripcion)->persona;
            if($grupoRol = $persona->grupoAsignadoEnActividad($idActividad))
            {
                $grupoRol->idGrupo = $datos['grupo']['idGrupo'];
                $grupoRol->save();
            } else {
                //Nuevo
                $grupoRol = new GrupoRolPersona();
                $grupoRol->idPersona = $persona->idPersona;
                $grupoRol->idActividad = $idActividad;
                $grupoRol->idGrupo = $datos['grupo']['idGrupo'];
                $grupoRol->rol = "";
                $grupoRol->save();
            }
        }
        return response()
            ->json("Grupo " . $request->grupo['nombre']. " configurado a " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function asignarPunto($idActividad, Request $request)
    {
        foreach ($request->inscripciones as $idInscripcion) {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->idPuntoEncuentro = $request->punto;
            $inscripcion->save();
            Mail::to($inscripcion->persona->mail)->send(new ActualizacionActividad($inscripcion));
        }
        return response()
            ->json("Punto de encuentro actualizado en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarEstado(Request $request, $id)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->estado = $request->estado;
            $inscripcion->save();
        }
        return response()
            ->json("Estado actualizado a " . $request->estado . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarAsistencia(Request $request, $id)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->presente = $request->asistencia;
            $inscripcion->save();
        }

        $msgAsistencia = $request->asistencia === 1 ? "Presente" : "Ausente";
        return response()
            ->json("Asistencia actualizada a " . $msgAsistencia . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function getInscriptos($id, Request $request)
    {
        if($request->has('inscriptos')){
            $filtros['inscriptos'] = $request->inscriptos;
            $filtros['idActividad'] = $id;
        }

        $export = new InscripcionesExport($filtros);
        $collection = $export->collection();
//        $result = $collection->only(['idPersona', 'nombres', 'apellidoPaterno']);
        return $collection;

    }

    public function store($id, Request $request)
    {
        $user = Persona::findOrFail($request->idPersona);
        $yaInscripto = Inscripcion::where('idPersona', '=', $request->idPersona)
            ->where('idActividad', '=', $id)
            ->first();
        if ($yaInscripto) {
            return response('Voluntario ya inscripto', 428);
        }
        $inscripcion = $request->all();
        $inscripcion = $this->inscribir($inscripcion);
        $grupo = $this->incluirEnGrupo($inscripcion);
        if ($inscripcion &&  $grupo) {
            Mail::to($user->mail)->send(new MailConfimacionInscripcion($inscripcion));
            return response('ok');
        }

        return response('Error al guardar la Inscripción', 500);
    }

    private function incluirEnGrupo($request)
    {
        $arr = [
            'idPersona' => (int)$request['idPersona'],
            'idGrupo' => (int)$request['idGrupo'],
            'idActividad' => (int)$request['idActividad'],
            'rol' => $request['rol']
        ];

        return GrupoRolPersona::create($arr);
    }

    private function inscribir($inscripcion)
    {
        $data = [
            'idActividad'       => (int)$inscripcion['idActividad'],
            'idPersona'         => (int)$inscripcion['idPersona'],
            'fechaInscripcion'  => Carbon::now(),
            'idPersonaModificacion' => auth()->user()->idPersona,
            'idPuntoEncuentro'  => $inscripcion['idPuntoEncuentro'],
            'estado'            => 'Sin Contactar',
            'evaluacion'        => 0,
            'acompanante'       => ''
        ];

        return Inscripcion::create($data);
    }

    public function procesarArchivo($id, Request $request)
    {
        $this->validate($request, array(
            'archivo' => 'required'
        ));

        if($request->has('archivo')){
            $extension = File::extension($request->archivo->getClientOriginalName());
            if (in_array($extension, array("xlsx", "xls", "csv"))) {
                $path = $request->archivo->getRealPath();
                $data = (new FastExcel)->import($path);
                $procesados = 0;
                if(!empty($data) && $data->count()){
                    //Borra logs del procesamiento anterior
                    DB::table('atl_logs')
                        ->where('idPersona', auth()->user()->idPersona)
                        ->where('nombreProceso', 'importar_inscripciones')
                        ->delete();
                    $counter = 0;
                    $errores = [];
                    foreach($data as $inscripcion){
                        $errorEnRegistro = false;
                        $counter++;
                        try {
                            $persona = Persona::where('mail', $inscripcion['email'])->firstOrFail();
                        } catch(ModelNotFoundException $e) {
                            $errores[] = "Error en linea " . $counter . ". Mail no encontrado";
                            $errorEnRegistro = true;
                        }

                        try {
                            $punto = PuntoEncuentro::where('punto', $inscripcion['punto'])
                                ->where('idActividad', $id)
                                ->firstOrFail();
                        } catch (ModelNotFoundException $e) {
                            $errores[] = "Error en linea " . $counter . ". Punto de encuentro no encontrado";
                            $errorEnRegistro = true;
                        }

                        try {
                            $grupo = Grupo::where('nombre', $inscripcion['grupo'])
                                ->where('idActividad', $id)
                                ->firstOrFail();
                        } catch (ModelNotFoundException $e) {
                            if(empty($inscripcion['grupo'])){
                                $grupo = "";
                            } else {
                                $errores[] = "Error en linea " . $counter . ". Grupo no encontrado. Corregir o dejar en blanco para asignarlo al grupo raíz.";
                                $errorEnRegistro = true;
                            }
                        }
                        if(!$errorEnRegistro) {
                            $inscripcionValida = [
                                'idActividad' => $id,
                                'idPersona' => $persona->idPersona,
                                'idPuntoEncuentro' => $punto->idPuntoEncuentro,
                                'idGrupo' => ($grupo instanceof Grupo) ? $grupo->idGrupo : 0,
                                'rol' => $inscripcion['rol']
                            ];

                            if($persona->noEstaInscripto($id)) {

                                $inscripto = $this->inscribir($inscripcionValida);

                                if ($inscripto) { //Enviar mail al voluntario
                                    Mail::to($persona->mail)->send(new MailConfimacionInscripcion($inscripto));
                                } else {
                                    $errores[] = "Error interno al inscribir a " . $persona->nombreCompleto . "(" . $persona->mail. ")";
                                }

                                $incluidoEnGrupo = $this->incluirEnGrupo($inscripcionValida);

                                if(empty($incluidoEnGrupo)){
                                    $errores[] = "Error interno al incluir a " . $persona->nombreCompleto . "(" . $persona->mail. ") en el grupo " . $inscripcion['grupo'];
                                }
                                if($inscripto && $incluidoEnGrupo) {
                                    $procesados++;
                                }

                            } else {
                                $errores[] = "Error en linea " . $counter . ". Voluntario ya inscripto a la actividad.";
                            }
                        }
                    } //end foreach
                    foreach ($errores as $error){
                        Log::create([
                            'idPersona' => auth()->user()->idPersona,
                            'nombreProceso' => 'importar_inscripciones',
                            'detalle' => $error
                        ]);
                    }
                    return response()->json(
                        [
                            'mensaje' => "Se procesaron " . $procesados . " registros de " . $counter . " correctamente."
                                           . " Se encontraron " . count($errores) . " errores." ,
                            'log_link' => route('logs', 'importar_inscripciones'),
                            'errores' => count($errores)
                        ], 200);
                } //end if hay datos
                return response()->json([
                    'mensaje' => "No hay datos para procesar"
                ], 200);
            }
            return response()->json([
                'mensaje' => "Extensión de archivo inválida"
            ], 422);
        }
    }
}
