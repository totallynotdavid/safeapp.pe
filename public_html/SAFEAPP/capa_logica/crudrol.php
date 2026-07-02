<?php

class crudrol {

    public function agregar($datos) {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = "INSERT into rol (nombre_rol) values ('$datos[0]')";
        return sqlsrv_query($conexion, $sql);
    }

    public function obtenDatos($id) {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT id_rol, nombre_rol from rol where id_rol='$id'";
        $result = sqlsrv_query($conexion, $sql);
        $ver = sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);
        $datos = array(
            'id_rol' => $ver[0],
            'nombre_rol' => $ver[1]);
        return $datos;
    }

    public function actualizar($datos) {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "UPDATE rol set nombre_rol = '$datos[1]'
						where id_rol = '$datos[0]'";
        return sqlsrv_query($conexion, $sql);
    }

    public function eliminar($id) {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "DELETE from rol where id_rol='$id'";
        return sqlsrv_query($conexion, $sql);
    }

}

?>