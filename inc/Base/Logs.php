<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;

class Logs
{

    public static function register($message)
    {
        $log_dir = trailingslashit(LLKI_PLUGIN_PATH) . 'log';

        // Crear la carpeta 'log' dentro del directorio del plugin si no existe
        wp_mkdir_p($log_dir);

        // Definir la ruta y nombre de archivo del archivo de registro
        $log_file = trailingslashit($log_dir) . 'plugin-error.log';

        // Abrir el archivo de registro en modo escritura (si no existe, se crea automáticamente)
        $handle = fopen($log_file, 'a');

        if ($handle) {
            // Agregar fecha y hora al mensaje de registro
            $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

            // Escribir el mensaje en el archivo de registro
            fwrite($handle, $log_message);

            // Cerrar el archivo de registro
            fclose($handle);
        }
    }
    
}
