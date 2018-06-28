@extends('backoffice.main')

@section('page_title', 'Usuarios')

@section('add-new')

    <span class="pull-right">
        <a href="" class="btn btn-primary btn-lg">
            {{--href="/admin/usuarios/crear"--}}
            <i class="fa fa-plus"></i> Nuevo Usuario
        </a>
    </span>
@endsection

@section('content')
    <div class="box">
        <div class="box-body  with-border">
            <datatable
                    api-url="/admin/ajax/usuarios/"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="Buscar por nombre, apellido o documento"
                    detail-url="/admin/usuarios/"
            ></datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection