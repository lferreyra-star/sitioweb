<?php
/**
 * Configuración general del sitio: textos, datos de contacto y apariencia.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Correo;
use App\Database;
use App\Repos\ConfigRepo;

Auth::exigirLogin();
verificar_csrf();

$repo = new ConfigRepo();

/** Campos editables: clave => [etiqueta, tipo, máximo, opciones?]. */
$campos = [
    'titulos' => [
        'sitio_titulo'     => ['Título del sitio (pestaña del navegador)', 'text', 160],
        'meta_descripcion' => ['Meta descripción (SEO)', 'text', 300],
        'logo_primero'     => ['Logo: parte 1', 'text', 40],
        'logo_segundo'     => ['Logo: parte 2 (resaltada)', 'text', 40],
    ],
    'hero' => [
        'hero_saludo'      => ['Saludo', 'text', 60],
        'hero_nombre_grad' => ['Nombre con degradado', 'text', 60],
        'hero_nombre_resto'=> ['Resto del nombre', 'text', 60],
        'hero_roles'       => ['Roles del efecto máquina de escribir (separados por |)', 'text', 200],
        'hero_descripcion' => ['Descripción del hero', 'textarea', 400],
    ],
    'sobre' => [
        'sobre_parrafo_1'  => ['Sobre mí · párrafo 1 (admite <strong>, <em>)', 'textarea', 900],
        'sobre_parrafo_2'  => ['Sobre mí · párrafo 2', 'textarea', 900],
        'perfil_nombre'    => ['Nombre completo', 'text', 120],
        'perfil_perfil'    => ['Perfil profesional', 'text', 80],
        'perfil_idiomas'   => ['Idiomas', 'text', 80],
        'perfil_email'     => ['Email público', 'email', 190],
    ],
    'contacto' => [
        'contacto_titulo'  => ['Texto de la sección de contacto', 'text', 200],
        'whatsapp'         => ['WhatsApp (solo números, con código de país)', 'text', 20],
        'foto'             => ['Ruta de la foto', 'text', 200],
        'footer_texto'     => ['Texto del pie de página', 'text', 200],
    ],
    'correo' => [
        'smtp_host'      => ['Servidor SMTP (ej. smtp.gmail.com)', 'text', 190],
        'smtp_puerto'    => ['Puerto SMTP', 'number', 5],
        'smtp_seguridad' => ['Seguridad', 'select', 10, ['tls' => 'TLS (recomendado)', 'ssl' => 'SSL', 'ninguna' => 'Ninguna']],
        'smtp_usuario'   => ['Usuario SMTP', 'text', 190],
        'smtp_clave'     => ['Contraseña SMTP', 'password', 200],
        'smtp_remitente' => ['Remitente (correo)', 'email', 190],
        'smtp_nombre'    => ['Nombre del remitente', 'text', 120],
    ],
];

/* ------------------------- Procesar POST (PRG) ------------------------- */
if (es_post()) {
    $guardar = [];

    foreach ($campos as $grupo) {
        foreach ($grupo as $clave => $cfgCampo) {
            $etiqueta = $cfgCampo[0];
            $tipo     = $cfgCampo[1];
            $maximo   = (int) $cfgCampo[2];
            $opciones = $cfgCampo[3] ?? null;

            $valor = post($clave);

            if (mb_strlen($valor) > $maximo) {
                flash('error', '«' . $etiqueta . '» supera el máximo de ' . $maximo . ' caracteres.');
                ir_a('admin/sitio.php');
            }
            if ($tipo === 'email' && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                flash('error', '«' . $etiqueta . '» debe ser un correo válido.');
                ir_a('admin/sitio.php');
            }
            if ($clave === 'whatsapp' && $valor !== '' && !preg_match('/^\+?[0-9\s\-]{5,20}$/', $valor)) {
                flash('error', 'El WhatsApp solo debe contener números (con código de país).');
                ir_a('admin/sitio.php');
            }
            if ($clave === 'smtp_puerto') {
                if ($valor !== '' && (!ctype_digit($valor) || (int)$valor < 1 || (int)$valor > 65535)) {
                    flash('error', 'El puerto SMTP debe estar entre 1 y 65535.');
                    ir_a('admin/sitio.php');
                }
            }
            if ($clave === 'smtp_seguridad') {
                $permitidas = ['tls', 'ssl', 'ninguna'];
                if ($valor !== '' && !in_array($valor, $permitidas, true)) {
                    flash('error', 'La seguridad SMTP debe ser: tls, ssl o ninguna.');
                    ir_a('admin/sitio.php');
                }
            }

            $guardar[$clave] = $valor;
        }
    }

    $repo->guardarVarios($guardar);
    $accion = post('accion');

    if ($accion === 'probar-correo') {
        $usuario = Auth::usuario();
        $destino = $usuario['email'] ?? '';
        if ($destino === '') {
            flash('error', 'No tienes un correo electrónico configurado en tu cuenta de administrador.');
        } else {
            $resultado = Correo::enviar($destino, 'Prueba de correo — Portafolio', '<p>¡Éxito!</p><p>Este es un correo de prueba enviado desde tu panel de administración.</p>');
            if ($resultado['ok']) {
                flash('exito', 'Correo de prueba enviado a ' . $destino . '. Comprueba tu bandeja de entrada.');
            } else {
                flash('error', 'No se pudo enviar el correo de prueba: ' . $resultado['error']);
            }
        }
        ir_a('admin/sitio.php');
    }

    flash('exito', 'Configuración guardada correctamente.');
    ir_a('admin/sitio.php');
}

$config = $repo->comoArreglo();

$titulo = 'Configuración del sitio';
$pagina = 'sitio';
require __DIR__ . '/partials/inicio.php';
?>

<form class="form-admin" method="post" action="<?= e(url('admin/sitio.php')) ?>">
  <?= csrf_campo() ?>

  <?php foreach ($campos as $claveGrupo => $grupo): ?>
    <section class="caja">
      <header class="caja__cabecera">
        <h2>
          <?= e([
              'titulos'  => '🌐 Identidad y SEO',
              'hero'     => '👋 Sección principal (hero)',
              'sobre'    => '👤 Sobre mí y perfil',
              'contacto' => '✉️ Contacto y pie',
              'correo'   => '📧 Correo saliente (SMTP)',
          ][$claveGrupo]) ?>
        </h2>
      </header>

      <div class="form-admin__grid">
        <?php foreach ($grupo as $clave => $cfgCampo): ?>
          <?php $etiqueta = $cfgCampo[0]; $tipo = $cfgCampo[1]; $maximo = (int) $cfgCampo[2]; $opciones = $cfgCampo[3] ?? null; ?>
          <label class="<?= $tipo === 'textarea' ? 'form-admin__ancho' : '' ?>">
            <span><?= e($etiqueta) ?></span>
            <?php if ($tipo === 'textarea'): ?>
              <textarea name="<?= e($clave) ?>" rows="3" maxlength="<?= $maximo ?>"><?= e($config[$clave] ?? '') ?></textarea>
            <?php elseif ($tipo === 'select' && is_array($opciones)): ?>
              <select name="<?= e($clave) ?>">
                <?php foreach ($opciones as $valOpt => $textoOpt): ?>
                  <option value="<?= e($valOpt) ?>" <?= (($config[$clave] ?? '') === $valOpt) ? 'selected' : '' ?>><?= e($textoOpt) ?></option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <input type="<?= e($tipo) ?>" name="<?= e($clave) ?>" maxlength="<?= $maximo ?>"
                     value="<?= ($tipo === 'password') ? (string) ($config[$clave] ?? '') : e($config[$clave] ?? '') ?>"
                     <?= $tipo === 'number' ? 'min="1" max="65535"' : '' ?>>
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if ($claveGrupo === 'correo'): ?>
        <footer class="caja__pie">
          <p class="form-admin__nota">Si no configuras SMTP, se usará <code>mail()</code> (puede no funcionar en localhost). El envío siempre se guarda en <code>storage/correos.log</code>.</p>
          <button type="submit" class="boton" name="accion" value="probar-correo">Enviar correo de prueba al administrador</button>
        </footer>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <div class="form-admin__acciones form-admin__acciones--fijo">
    <button type="submit" class="boton boton--primario">Guardar toda la configuración</button>
    <span class="form-admin__nota">Los cambios se aplican de inmediato al sitio público.</span>
  </div>
</form>

<?php require __DIR__ . '/partials/fin.php'; ?>
