<?php

namespace App;

use App\Mail\ForgotPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;

class Persona extends Authenticatable
{
    use Notifiable, HasRoles;
    protected $table = 'Persona';
    protected $primaryKey = 'idPersona';

    public function puntosEncuentro()
    {
        return $this->hasMany(PuntoEncuentro::class, 'idPersona');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'idPersona');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idCoordinador');
    }

    public function gruposRoles()
    {
        return $this->hasMany(GrupoRolPersona::class, 'idPersona', 'idPersona');
    }

    public function getNombreCompletoAttribute() {
        return $this->nombres . ' ' . $this->apellidoPaterno;
    }

    public function grupoAsignadoEnActividad($idActividad)
    {
        return $this->gruposRoles()->where('idActividad', $idActividad)->first();
    }

    public function estaInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->whereNotIn('estado',['Desinscripto'])->count();
    }

    public function noEstaInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->whereNotIn('estado',['Desinscripto'])->count() == 0;
    }

    public function verificacion()
    {
        return $this->hasOne('App\VerificacionMailPersona', 'idPersona');
    }

    public function perfil() {
        $usuario = [
            'id' => $this->idPersona,
            'email' => $this->mail,
            'nombre' => $this->nombres,
            'apellido' => $this->apellidoPaterno,
            'nacimiento' => $this->fechaNacimiento,
            'sexo' => $this->sexo,
            'dni' => $this->dni,
            'pais' => $this->idPais,
            'provincia' => $this->idProvincia,
            'localidad' => $this->idLocalidad,
            'telefono' => $this->telefonoMovil,
            'pass' => ''
        ];
        return $usuario;
    }

    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->mail)->send(new ForgotPassword($token));
    }

    public function getEmailForPasswordReset()
    {
        return $this->mail;
    }
}
