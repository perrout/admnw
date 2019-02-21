<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/pt-br:Editando_wp-config.php
 *
 * @package WordPress
 */



/* PHP Memory */
define( 'WP_MEMORY_LIMIT', 		'64M' );
define( 'WP_MAX_MEMORY_LIMIT', 	'256M' );

/* Custom WordPress URL. */
define( 'WP_SITEURL', 'http://dev.gcm.campos.rj.gov.br' );
define( 'WP_HOME', 'http://dev.gcm.campos.rj.gov.br' );

// ** Configurações do MySQL - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define('DB_NAME', 'adora_db');
/** Usuário do banco de dados MySQL */
define('DB_USER', 'root');
/** Senha do banco de dados MySQL */
define('DB_PASSWORD', 'root');
/** Nome do host do MySQL */
define('DB_HOST', 'adora-dev-db');
/** Charset do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8mb4');
/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define('DB_COLLATE', '');

/* Disable Post Revisions. */
define( 'WP_POST_REVISIONS', false);

/* Content Path */
define( 'WP_CONTENT_URL', '/wp-content' );

/* Media Trash. */
define( 'MEDIA_TRASH', true );
/* Trash Days. */
define( 'EMPTY_TRASH_DAYS', 	'1' );
// define( 'DISALLOW_FILE_EDIT', true );
// define( 'DISALLOW_FILE_MODS', true );

ini_set('max_execution_time', 300); //300 seconds = 5 minutes



/* CRON */
define( 'DISABLE_WP_CRON',      'true' );

// Contact Form 7 
// define ('WPCF7_LOAD_JS', false); // Added to disable JS loading
// define ('WPCF7_LOAD_CSS', false); // Added to disable CSS loading

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'R.U[L;;#GLFTaK<V,coM(-Lmm,<>Xa`J!j4B[Hneq{S#(itJCi_-PKG`&}+p@GgB');
define('SECURE_AUTH_KEY',  'o%TH,@5_6;,QE]A1 [Q`|X*w=W!m#:/KJxO5n%-P!=oO|]rt.Nh}`(pxon1k}iai');
define('LOGGED_IN_KEY',    'Wm9L%/rI/Osrg^/u:=lJbXOwO-x!3`DpN7k&OwG%/C/BQ]T3=;A3$=] tcTk%K<G');
define('NONCE_KEY',        'OjI@b/Uq?C?Z-4 GJ0Y}2ujreP +~$;u&!@l%6KNx>!(>=dk_;7tg%vQEngFE(fD');
define('AUTH_SALT',        'vQKA=eiNsT60=~Z=G{tGbRVKamX4{6#b5]1OhKr/xs?: #d(8CA~Cl]|S37i`b3+');
define('SECURE_AUTH_SALT', 'dsd<{IA46&(iavh-?(QN-34!=Mq!@6/S#M+ru/*T7:)wfOo;u12 j[*Ja)F?%pFc');
define('LOGGED_IN_SALT',   '83_DdO VHswAdR|fz_;gZe{Ib~Oju0.6Q5gW=7Z}^&:GPY?XmoqQHV-]=za,Tq)s');
define('NONCE_SALT',       'Ul|1q.,@f`*~]SpP184<Hmb99(h(7=[XwfdIP$}0zjzvSVHN2;CX;hKuq!diL0/|');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix  = 'wp_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://codex.wordpress.org/pt-br:Depura%C3%A7%C3%A3o_no_WordPress
 */
define( 'WP_DEBUG',         true );
define( 'WP_DEBUG_LOG',     true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'SCRIPT_DEBUG',     true );
define( 'SAVEQUERIES',      true );
/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Configura as variáveis e arquivos do WordPress. */
require_once(ABSPATH . 'wp-settings.php');
