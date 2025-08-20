<?php
$user_has_session = isset($_COOKIE["PHPSESSID"]) && !empty($_COOKIE["PHPSESSID"]);

if ($user_has_session)
	session_start();

if ((!$user_has_session || !$_SESSION["logged_in"]) && !isset($_GET["bypassauth"])) {
	header("location: /login");
	die;
}
?><?php if (!isset($_GET["bypassauth"])) { ?><a href="/login?logout">Log Out</a><?php } ?>
<style>input[type="text"] { width: 500px }</style>
<br>
<h1>Vulnerable Application</h1>

<h2>Redirect</h2>
<form method="get" action="/api/redirect">
	<input type="text" name="url" placeholder="https://google.com">
	<input type="submit">
</form>

<h2>XSS</h2>
<form method="post" action="">
	<input type="text" name="input" placeholder="&lt;script&gt;alert(&quot;Hello&quot;); window.location = &quot;https://google.com&quot;;&lt;/script&gt;">
	<input type="submit">
</form>
<?php if (isset($_POST["input"])) { ?>
<div>Your input: <pre><?= htmlentities($_POST["input"] ?? "") ?></div>
<div>Rendered: <pre><?= $_POST["input"] ?? "" ?></div>
<?php } ?>
