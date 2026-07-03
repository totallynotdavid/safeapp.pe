<?php

class crudproducto
{
    public function obtenDatosSuptipo()
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT subtipo FROM `producto` WHERE `tipo`='FIJA' GROUP BY subtipo ";

        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function obtenDatosSuptipoAv()
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT subtipo FROM `producto` WHERE `tipo`='AVANZADO' GROUP BY subtipo ";

        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function obtenDatosDescripcionAv($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT descripcion FROM `producto` WHERE `tipo`='AVANZADO'
         and subtipo='$dat' GROUP BY descripcion ";

        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function obtenDatosDescripcion($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT descripcion FROM `producto` WHERE `tipo`='FIJA'
         and subtipo='$dat' GROUP BY descripcion ";

        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function obtenDatoscf($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT cf FROM `producto` WHERE `tipo`='FIJA'
         and subtipo='$dat[0]' and descripcion='$dat[1]' and estado=1 ";

        $result = mysqli_query($conexion, $sql);

        $ver = mysqli_fetch_array($result);

        $datos = array(
            'cf' => $ver[0],
        );
        return $datos;
    }

    public function obtenDatoscfAv($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT cf FROM `producto` WHERE `tipo`='AVANZADO'
         and subtipo='$dat[0]' and descripcion='$dat[1]' and estado=1 ";

        $result = mysqli_query($conexion, $sql);

        $ver = mysqli_fetch_array($result);

        $datos = array(
            'cf' => $ver[0],
        );
        return $datos;
    }
}
