<?php

class Conexion
{
    // Almacena la única instancia PDO durante toda la ejecución
    private static ?PDO $instance = null;

    // Impide crear instancias con "new Conexion()" desde fuera
    private function __construct() {}

    // Impide duplicar la instancia con clone
    private function __clone() {}

    // Retorna la instancia PDO existente o crea una nueva si no existe
    public static function getInstance(): PDO
    {
        // Si la instancia existe, verificar que la conexión sigue viva
        // "Packets out of order" ocurre cuando MySQL cerró la conexión
        // por inactividad pero PDO sigue usando la instancia "muerta"
        if (self::$instance !== null) {
            try {
                self::$instance->query('SELECT 1');
            } catch (PDOException $e) {
                // Conexión muerta — forzar reconexión
                self::$instance = null;
            }
        }

        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST
                     . ';port='      . DB_PORT
                     . ';dbname='    . DB_NAME
                     . ';charset='   . DB_CHARSET;

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    // Lanza excepciones en lugar de fallar silenciosamente
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Retorna filas como arrays asociativos
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Usa prepared statements reales — previene SQL injection
                    PDO::ATTR_EMULATE_PREPARES   => false,

                    // CRÍTICO: false elimina el "Packets out of order"
                    // Las conexiones persistentes reutilizan conexiones que
                    // MySQL ya cerró por timeout — causa el error
                    PDO::ATTR_PERSISTENT         => false,

                    // Timeout de conexión — evita cuelgues si MySQL no responde
                    PDO::ATTR_TIMEOUT            => 10,

                    // Charset a nivel de driver — refuerza la configuración
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);

                self::$instance->exec("SET collation_connection = utf8mb4_unicode_ci");

            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('Error de conexión: ' . $e->getMessage());
                } else {
                    die('Error de conexión. Contacte al administrador.');
                }
            }
        }

        return self::$instance;
    }

    // Cierra la conexión PDO liberando el recurso
    public static function close(): void
    {
        self::$instance = null;
    }
}