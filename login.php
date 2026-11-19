<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['usuario'] == 'admin' && $_POST['senha'] == '1234') {
        $_SESSION['logado'] = true;
        header('Location: index.php');
        exit;
    } else {
        $erro = "Usuário ou senha errados!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login - Gerenciador Logístico</title>
<style>
body { font-family: Arial; background: #1e3a5f; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
.caixa { background: white; padding: 30px; border-radius: 10px; width: 300px; text-align: center; }
input { width: 90%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; }
button { width: 100%; padding: 12px; background: #ff6b00; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
.erro { color: red; }
</style>
</head>
<body>
<div class="caixa">
<h2>🚚 Gerenciador<br>Logístico</h2>
<?php if(isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
<form method="POST">
<input type="text" name="usuario" placeholder="Usuário" required>
<input type="password" name="senha" placeholder="Senha" required>
<button type="submit">Entrar</button>
</form>
<p style="font-size:12px; color:#888;">Teste: admin / 1234</p>
</div>
</body>
</html>