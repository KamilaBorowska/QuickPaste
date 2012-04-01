<?php
// This code is also sort of quick. I'm really sorry for this.
// (C) 2012 - GlitchMr

// Fix magic quotes
if (get_magic_quotes_gpc()) {
    foreach ($_POST as &$value) {
        $value = stripslashes($value);
    }
    unset($value);
}

date_default_timezone_set('UTC');

// Installation script
if (!file_exists('more/config.php')) {
    copy('more/config.sample.php', 'more/config.php');
}

// Now for rest
function query($query) {
    static $created = false;
    global $db;
    $args = array_slice(func_get_args(), 1);
    $statement = $db->prepare($query);
    $statement->execute($args);
    if ((int) $statement->errorCode()) {
        if ($created) {
            die('Something went seriously wrong. Try refreshing site.');
        }
        $db->query('
            CREATE TABLE `pastes` (
                `name` VARCHAR(20),
                `paste` mediumtext,
                `date` TIMESTAMP
            );
        ');
        $created = true;
        call_user_func_array('query', func_get_args());
    }
    return $statement->fetchAll();
}

header('Content-type: text/html;charset=utf-8');
require('more/config.php');

$id = substr($_SERVER['REQUEST_URI'], 1);
if (isset($_POST['paste'])) {
    $random = substr(md5(crypt(rand(0,10))), 0, 8);
    query('INSERT INTO pastes (paste, date, name) VALUES (?, ?, ?)',
           $_POST['paste'], date('c'), $random);
    header("Location: /$random");
    exit;
}

if ($id && $_SERVER['REQUEST_URI'] !== '/') {
    if ($id === 0) {
        $paste = array();
    }
    else {
        $paste = query('SELECT paste
                        FROM pastes
                        WHERE name = ?
                        LIMIT 1',
                        $id);
    }
    if ($paste) header('HTTP/1.0 404 Not Found');
}
?>
<!DOCTYPE html>
<title>qUicK Paste</title>
<link rel="stylesheet" href="/highlight/default.css">
<link rel="stylesheet" href="/more/style.css">
<div class="content">
<h1><a href="/">qUicK Paste</a></h1>
<?php
if (isset($paste)) {
    if ($paste) {
        $paste = $paste[0]['paste'];
        $paste = htmlspecialchars($paste);
        $message = preg_replace('{https?://[^\s]*[^.,!?):"\']+}i',
                                '<a href="\0">\0</a>', $paste);
        echo '<pre><code>', $message, '</code></pre>';
    }
    else {
        $message = file_get_contents('more/404.txt');
        $message = explode("===", $message);
        $message = trim($message[array_rand($message)]);
        $message = str_replace('[URL]', $_SERVER['REQUEST_URI'], $message);
        $message = htmlspecialchars($message);
        echo "<pre><code>$message</code></pre>";
    }
}
?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
" method="post">
<textarea name="paste" placeholder=
"Paste your code. You don't have to choose language."><?php
if (isset($paste) && $paste !== array()) {
    echo $paste;
}
?></textarea>
<input type="submit" value="Paste!">
</form>
</div>
<ul><li><a href="/fnaq">fnaq</a>
    <li><a href="https://github.com/GlitchMr/quickpaste">source code</a></ul>
<script src="/more/ZeroClipboard.js"></script>
<script src="/highlight/highlight.js"></script>
<script src="/more/script.js"></script>
