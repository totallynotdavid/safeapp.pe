<?php

require_once 'clave.php';

class crudusuario
{
    public function agregarUsuario($datos)
    {
        $clave = hashearClave($datos[2]);
        $personal = strtoupper($datos[0]);
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sqlvalidar = 'SELECT count(*) FROM usuario WHERE usuario = ?';
        $stmt = mysqli_prepare($conexion, $sqlvalidar);
        mysqli_stmt_bind_param($stmt, 's', $datos[1]);
        mysqli_stmt_execute($stmt);
        $usuario = mysqli_fetch_array(mysqli_stmt_get_result($stmt));

        if ($usuario[0] != 0) {
            return 1;
        }

        $sql = 'INSERT into usuario (usuario, clave, id_rol, personal, estado, sexo, id_supervisor)
            values (?, ?, ?, ?, ?, ?, ?)';
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'sssssss',
            $datos[1],
            $clave,
            $datos[3],
            $personal,
            $datos[7],
            $datos[6],
            $datos[5],
        );

        return mysqli_stmt_execute($stmt);
    }

    public function agregarRegion($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sqlvalidar = "SELECT id_usuario FROM usuario WHERE usuario='$datos[1]'";
        $result = mysqli_query($conexion, $sqlvalidar);
        $usuario = mysqli_fetch_array($result);

        $sql = "INSERT into asigna (id_tienda, id_usuario, estado) 
            values ('$datos[4]', '$usuario[0]',  0 )";

        return mysqli_query($conexion, $sql);
    }

    public function agregarAcceso($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sqlusuario = "SELECT id_usuario FROM usuario WHERE usuario='$datos[1]'";
        $result = mysqli_query($conexion, $sqlusuario);
        $usuario = mysqli_fetch_array($result);
        $usuario = $usuario[0];

        $sqlmenu = "SELECT id_menu FROM accesoxrol WHERE id_rol='$datos[3]'";
        $resultmenu = mysqli_query($conexion, $sqlmenu);

        while ($mostrar = mysqli_fetch_array($resultmenu)) {
            $id_menu = $mostrar[0];

            $sql = "INSERT into acceso (id_menu, id_usuario) 
            values ('$id_menu', '$usuario' )";

            $resultsql = mysqli_query($conexion, $sql);
        }

        return 'ok';
    }

    public function obtenDatos($id)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT id_usuario, usuario,  personal ,id_supervisor from usuario where id_usuario='$id'";
        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);
        $datos = array(
            'id_usuario' => $ver[0],
            'usuario' => $ver[1],
            'personal' => $ver[2],
            'id_supervisor' => $ver[3],
        );
        return $datos;
    }

    public function obtenDatosPersonal($id)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT  id_usuario, 
                        usuario,  
                        personal,
                        fecha_nacimiento,
                        telefono,
                        correo 
                        from usuario 
                        where id_usuario='$id'";
        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);
        $datos = array(
            'id_usuario' => $ver[0],
            'usuario' => $ver[1],
            'personal' => $ver[2],
            'fecha_nacimiento' => $ver[3],
            'telefono' => $ver[4],
            'correo' => $ver[5],
        );
        return $datos;
    }

    public function obtenDatosRol()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $datos = array();
        $sql = 'SELECT id_rol,nombre_rol from rol ';

        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function obtenDatosRegion()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $datos = array();
        $sql = 'SELECT id_tienda,nombre from tienda ';
        $result = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }
        return $datos;
    }

    // agregado diciembre 2022

    public function obtenDatosEtapa()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $datos = array();
        $sql = 'SELECT id_etapa, nombre_etapa from etapa';
        $result = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }
        return $datos;
    }

    public function obtenDatosSupervisor()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $datos = array();
        $sql = 'SELECT id_supervisor,nombre from supervisor ';

        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function actualizarUsuario($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $clave = hashearClave($datos[2]);

        $sql = 'UPDATE usuario set
        clave = ?,
        personal = ?,
        estado = ?,
        id_supervisor = ?
        where id_usuario = ?';
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'sssss', $clave, $datos[1], $datos[4], $datos[3], $datos[0]);

        return mysqli_stmt_execute($stmt);
    }

    public function actualizarPersonal($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "UPDATE      usuario  set    
                            personal='$datos[1]', 
                            fecha_nacimiento='$datos[2]',
                            telefono='$datos[3]',
                            correo='$datos[4]'
                            where id_usuario='$datos[0]'";
        return mysqli_query($conexion, $sql);
    }

    public function obtenDatosback()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $datos = array();
        $sql = 'SELECT id_back,nombre from back_office ';
        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }
        return $datos;
    }

    public function eliminar($id)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = "DELETE from usuario where id_usuario='$id'";
        return mysqli_query($conexion, $sql);
    }
}
