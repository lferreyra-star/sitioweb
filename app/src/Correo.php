<?php
/**
 * Envío de correo saliente.
 */

declare(strict_types=1);

namespace App;

final class Correo
{
    /** @return array{ok: bool, error: string} */
    public static function enviar(string $para, string $asunto, string $cuerpoHtml): array
    {
        $para = trim($para);
        if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'La dirección de correo del destinatario no es válida.'];
        }

        $de = trim((string) cfg('smtp_remitente'));
        if ($de === '' || !filter_var($de, FILTER_VALIDATE_EMAIL)) {
            $de = 'no-reply@' . self::dominioLocal();
        }
        $nombreDe = trim((string) cfg('smtp_nombre')) ?: 'Portafolio';

        try {
            $resultado = self::configurado()
                ? self::porSmtp($de, $nombreDe, $para, $asunto, $cuerpoHtml)
                : self::porMail($de, $nombreDe, $para, $asunto, $cuerpoHtml);
        } catch (\Throwable $e) {
            $resultado = ['ok' => false, 'error' => 'Error inesperado al enviar el correo: ' . $e->getMessage()];
        }

        self::guardarCopia($para, $asunto, $cuerpoHtml, $resultado);
        return $resultado;
    }

    public static function configurado(): bool
    {
        return trim((string) cfg('smtp_host')) !== '';
    }

    private static function porMail(string $de, string $nombreDe, string $para, string $asunto, string $html): array
    {
        $cabeceras = self::cabeceras($de, $nombreDe, $para, $asunto);
        $ok = @mail($para, self::codificar($asunto), self::cuerpoBase64($html), $cabeceras);
        return $ok
            ? ['ok' => true, 'error' => '']
            : ['ok' => false, 'error' => 'La función mail() no está disponible. Configura SMTP.'];
    }

    private static function porSmtp(string $de, string $nombreDe, string $para, string $asunto, string $html): array
    {
        $host    = trim((string) cfg('smtp_host'));
        $puerto  = (int) (cfg('smtp_puerto') ?: 587);
        $seg     = strtolower(trim((string) cfg('smtp_seguridad')));
        $seg     = in_array($seg, ['ssl', 'tls', 'ninguna'], true) ? $seg : 'tls';
        $usuario = (string) cfg('smtp_usuario');
        $clave   = (string) cfg('smtp_clave');

        if ($puerto < 1 || $puerto > 65535) {
            return ['ok' => false, 'error' => 'Puerto SMTP inválido.'];
        }

        $transporte = $seg === 'ssl' ? 'ssl://' : '';
        $contexto   = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);

        $conn = @stream_socket_client($transporte . $host . ':' . $puerto, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $contexto);
        if ($conn === false) {
            return ['ok' => false, 'error' => "No se pudo conectar a $host:$puerto — $errstr"];
        }
        stream_set_timeout($conn, 15);

        $leer = static function () use ($conn): string {
            $datos = '';
            while (($l = fgets($conn, 1024)) !== false) {
                $datos .= $l;
                if (strlen($l) < 4 || $l[3] === ' ') break;
            }
            return $datos;
        };
        $codigo = static fn (string $r): int => (int) substr(trim($r), 0, 3);
        $cmd = static function (string $c) use ($conn, $leer, $codigo): array {
            fwrite($conn, $c . "\r\n");
            $r = $leer();
            return ['codigo' => $codigo($r), 'texto' => trim($r)];
        };

        if ($codigo($leer()) !== 220) { fclose($conn); return ['ok'=>false,'error'=>'SMTP no respondió 220']; }
        $cmd('EHLO localhost');
        if ($seg === 'tls') {
            $r = $cmd('STARTTLS'); if ($r['codigo']!==220) { fclose($conn); return ['ok'=>false,'error'=>'STARTTLS falló']; }
            if (!@stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($conn); return ['ok'=>false,'error'=>'TLS crypto falló']; }
            $cmd('EHLO localhost');
        }
        if ($usuario !== '') {
            $r=$cmd('AUTH LOGIN'); if ($r['codigo']!==334){fclose($conn);return ['ok'=>false,'error'=>'AUTH LOGIN no aceptado'];}
            $r=$cmd(base64_encode($usuario)); if ($r['codigo']!==334){fclose($conn);return ['ok'=>false,'error'=>'Usuario rechazado'];}
            $r=$cmd(base64_encode($clave)); if ($r['codigo']!==235){fclose($conn);return ['ok'=>false,'error'=>'Clave SMTP rechazada'];}
        }
        $r=$cmd('MAIL FROM:<' . $de . '>'); if ($r['codigo']!==250){fclose($conn);return ['ok'=>false,'error'=>'MAIL FROM rechazado'];}
        $r=$cmd('RCPT TO:<' . $para . '>'); if (!in_array($r['codigo'],[250,251],true)){fclose($conn);return ['ok'=>false,'error'=>'RCPT TO rechazado'];}
        $r=$cmd('DATA'); if ($r['codigo']!==354){fclose($conn);return ['ok'=>false,'error'=>'DATA no aceptado'];}
        $msg = self::cabeceras($de,$nombreDe,$para,$asunto)."\r\n\r\n".self::cuerpoBase64($html);
        $msg = preg_replace('/^\./m','..',$msg);
        fwrite($conn,$msg."\r\n.\r\n");
        $r=$leer(); if ($codigo($r)!==250){fclose($conn);return ['ok'=>false,'error'=>'Mensaje rechazado'];}
        $cmd('QUIT'); fclose($conn);
        return ['ok'=>true,'error'=>''];
    }

    private static function cabeceras(string $de, string $nombreDe, string $para, string $asunto): string
    {
        return implode("\r\n", [
            'Date: ' . date('r'),
            'From: ' . self::codificar($nombreDe) . ' <' . $de . '>',
            'To: <' . $para . '>',
            'Subject: ' . self::codificar($asunto),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'X-Mailer: Portafolio-PHP',
        ]);
    }

    private static function codificar(string $texto): string
    {
        return '=?UTF-8?B?' . base64_encode($texto) . '?=';
    }

    private static function cuerpoBase64(string $html): string
    {
        return chunk_split(base64_encode($html), 76, "\r\n");
    }

    private static function dominioLocal(): string
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $host = explode(':', $host)[0];
        return $host !== '' ? $host : 'localhost';
    }

    private static function guardarCopia(string $para, string $asunto, string $html, array $resultado): void
    {
        $ruta = STORAGE_PATH . '/correos.log';
        if (is_file($ruta) && filesize($ruta) > 524288) { @file_put_contents($ruta, ''); }
        $texto = strip_tags(str_replace(['<br>','<br/>','<br />','</p>'], ["\n","\n","\n","\n"], $html));
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $entrada = sprintf("%s\nPara: %s\nAsunto: %s\nEstado: %s%s\n%s\n%s\n",
            str_repeat('-', 72), $para, $asunto,
            $resultado['ok'] ? 'ENVIADO' : 'NO ENVIADO',
            $resultado['ok'] ? '' : ' | ' . $resultado['error'],
            str_repeat('-', 72), trim($texto)
        );
        @file_put_contents($ruta, $entrada . "\n", FILE_APPEND);
    }
}