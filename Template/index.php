<?php

/**
 * Template/index.php
 * Sistema de plantillas del panel admin
 * ZonaMarcol — DeskCod
 *
 * Divide el layout en dos partes usando {JBODY} como marcador.
 * $body[0] = todo antes de {JBODY} → head + sidebar + header
 * $body[1] = todo después de {JBODY} → footer + scripts
 */

if (!class_exists('Template')) {

    class Template {

        private array $body;

        public function __construct() {
            // Captura el output completo del layout base
            ob_start();
            include ROOT . 'Template' . DS . 'Default' . DS . 'index.php';
            $file = ob_get_clean();

            // Divide por el marcador {JBODY}
            $parts = explode('{JBODY}', $file);

            if (count($parts) !== 2) {
                error_log('[Template] ERROR: marcador {JBODY} no encontrado en Default/index.php');
                $this->body = [$file, ''];
            } else {
                $this->body = $parts;
            }

            // Emite la primera mitad — head + sidebar + header
            echo $this->body[0];
        }

        public function __destruct() {
            // Emite la segunda mitad — footer + scripts
            echo $this->body[1];
        }
    }

}

// Instancia la clase — dispara el constructor
$template = new Template();