<!-- Name Field -->
<div class="form-group col-sm-12">
    {!! Form::label('name', 'Rol:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>
<br>
<div class="form-group col-sm-12">
    <h3>Permisos</h3>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th><input type="checkbox" name="all" id="checkall" /> Marcar Todos</th>
                <th>Permiso</th>
            </tr>
            @php
                $groupedPermissions = $permisos->groupBy(function ($permiso) {
                    return explode(' ', $permiso->name)[0]; // Agrupa por la primera palabra del nombre del permiso
                });
            @endphp
            @foreach ($groupedPermissions as $group => $groupPermissions)
                <tr>
                    <td colspan="2">
                        <h4>{{ ucfirst($group) }}</h4> <!-- Título del grupo -->
                    </td>
                </tr>
                @foreach ($groupPermissions as $permisos)
                    @php
                        $sel = '';
                        if (isset($roles) && $roles->hasPermissionTo($permisos->name)) {
                            $sel = 'checked="checked"';
                        }
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" name="permiso_id[]" class="child" value="{!! $permisos->id !!}"
                                {!! $sel !!}>
                        </td>
                        <td>
                            {!! $permisos->name !!}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>
</div>
