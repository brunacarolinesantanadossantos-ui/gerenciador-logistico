<?php
session_start();
if (!isset($_SESSION['logado'])) {
    header('Location: login.php');
    exit;
}
$pdo = new PDO("mysql:host=localhost;dbname=gerenciador_logistico;charset=utf8mb4","root","");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$msg = "";

// BAIXAR RELATORIO DO DIA
if(isset($_GET['baixar_relatorio']) && !empty($_GET['data_relatorio'])){
    $data = $_GET['data_relatorio'];
    $s = $pdo->prepare("SELECT f.*, p.nome AS produto_nome, e.cliente AS cliente_nome, e.cidade, e.estado FROM fretes f LEFT JOIN produtos p ON p.id=f.produto_id LEFT JOIN enderecos e ON e.id=f.endereco_id WHERE DATE(f.data_frete)=?");
    $s->execute([$data]);
    $dados = $s->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_fretes_'.$data.'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['Data','Produto','Cliente','Cidade','Estado','Transportadora','Valor','Prazo'], ';');
    foreach($dados as $f){
        fputcsv($out, [$f['data_frete'], $f['produto_nome'], $f['cliente_nome'], $f['cidade'], $f['estado'], $f['transportadora'], $f['valor'], $f['prazo']], ';');
    }
    fclose($out);
    exit;
}

// CADASTRAR TRANSPORTADOR
if(isset($_POST['cadastrar_transportador'])){
    $s = $pdo->prepare("INSERT INTO transportadores (nome,cpf_cnpj,veiculo,placa) VALUES (?,?,?,?)");
    $s->execute([$_POST['nome'],$_POST['cpf_cnpj'],$_POST['veiculo'],$_POST['placa']]);
    $msg = "Transportador cadastrado!";
}
// CADASTRAR PRODUTO
if(isset($_POST['cadastrar_produto'])){
    $s = $pdo->prepare("INSERT INTO produtos (nome,codigo,peso,preco) VALUES (?,?,?,?)");
    $s->execute([$_POST['nome'],$_POST['codigo'],$_POST['peso'],$_POST['preco']]);
    $msg = "Produto cadastrado!";
}
// CADASTRAR ENDERECO
if(isset($_POST['cadastrar_endereco'])){
    $s = $pdo->prepare("INSERT INTO enderecos (cliente,cep,cidade,estado,rua,tipo,numero,bairro) VALUES (?,?,?,?,?,?,?,?)");
    $s->execute([$_POST['cliente'],$_POST['cep'],$_POST['cidade'],$_POST['estado'],$_POST['rua'],$_POST['tipo'],$_POST['numero'],$_POST['bairro']]);
    $msg = "Endereco cadastrado!";
}
// GERAR FRETE
if(isset($_POST['gerar_frete'])){
    $s = $pdo->prepare("INSERT INTO fretes (produto_id,endereco_id,transportadora,valor,prazo,data_frete) VALUES (?,?,?,?,?,CURDATE())");
    $s->execute([$_POST['produto_id'],$_POST['endereco_id'],$_POST['transportadora'],$_POST['valor'],$_POST['prazo']]);
    $msg = "Frete gerado!";
}

$transportadores = $pdo->query("SELECT * FROM transportadores")->fetchAll(PDO::FETCH_ASSOC);
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
$enderecos = $pdo->query("SELECT * FROM enderecos")->fetchAll(PDO::FETCH_ASSOC);
$fretes = $pdo->query("SELECT f.*, p.nome AS produto_nome, e.cliente AS cliente_nome, e.cidade FROM fretes f LEFT JOIN produtos p ON p.id=f.produto_id LEFT JOIN enderecos e ON e.id=f.endereco_id ORDER BY f.data_frete DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Gerenciador Logistico</title>
<style>body{font-family:Arial;max-width:900px;margin:20px auto;padding:10px}input,select{padding:8px;margin:4px;width:95%}button{padding:10px;background:#2563eb;color:#fff;border:0;border-radius:6px}h2{border-bottom:2px solid #2563eb}.card{border:1px solid #ddd;padding:12px;border-radius:8px;margin:12px 0}</style>
</head><body>
<h1>🚚 Gerenciador Logistico</h1>
<?php if($msg) echo "<b style='color:green'>$msg</b>"; ?>

<div class="card"><h2>📊 Relatorio de Fretes do Dia</h2>
<form method="get">
<input type="date" name="data_relatorio" required>
<button name="baixar_relatorio" value="1">Baixar Relatorio (Excel / CSV)</button>
</form>
<p>Escolha o dia e baixe todos os fretes daquele dia.</p>
</div>

<div class="card"><h2>Transportador</h2><form method="post">
<input name="nome" placeholder="Nome" required>
<input name="cpf_cnpj" placeholder="CPF / CNPJ" required>
<input name="veiculo" placeholder="Veiculo - ex: Caminhao">
<input name="placa" placeholder="Placa">
<button name="cadastrar_transportador">Cadastrar Transportador</button></form>
<?php foreach($transportadores as $t) echo "🚛 <b>".$t['nome']."</b> - ".$t['veiculo']." - ".$t['placa']."<br>"; ?>
</div>

<div class="card"><h2>Produto</h2><form method="post">
<input name="nome" placeholder="Nome do produto" required>
<input name="codigo" placeholder="Codigo">
<input name="peso" placeholder="Peso" type="number" step="0.01">
<input name="preco" placeholder="Preco" type="number" step="0.01">
<button name="cadastrar_produto">Cadastrar Produto</button></form>
<?php foreach($produtos as $p) echo "📦 <b>".$p['nome']."</b> - Cod: ".$p['codigo']." - Peso: ".$p['peso']." - R$ ".$p['preco']."<br>"; ?>
</div>

<div class="card"><h2>Endereco</h2><form method="post">
<input name="cliente" placeholder="Cliente" required>
<input name="cep" placeholder="CEP">
<input name="cidade" placeholder="Cidade">
<input name="estado" placeholder="Estado">
<input name="rua" placeholder="Rua">
<input name="numero" placeholder="Numero">
<input name="bairro" placeholder="Bairro">
<select name="tipo"><option value="origem">Origem</option><option value="destino">Destino</option></select>
<button name="cadastrar_endereco">Cadastrar Endereco</button></form>
<?php foreach($enderecos as $e) echo "📍 <b>".$e['cliente']."</b> - ".$e['tipo']." - ".$e['rua'].", ".$e['numero']." - ".$e['bairro']." - ".$e['cidade']."/".$e['estado']." - CEP: ".$e['cep']."<br>"; ?>
</div>

<div class="card"><h2>Gerar Frete</h2><form method="post">
<select name="produto_id" required><option value="">Escolha o produto</option><?php foreach($produtos as $p) echo "<option value='".$p['id']."'>".$p['nome']."</option>"; ?></select>
<select name="endereco_id" required><option value="">Escolha o endereco</option><?php foreach($enderecos as $e) echo "<option value='".$e['id']."'>".$e['cliente']." - ".$e['cidade']."</option>"; ?></select>
<select name="transportadora" required><option value="">Escolha a transportadora</option><?php foreach($transportadores as $t) echo "<option value='".$t['nome']."'>".$t['nome']." - ".$t['veiculo']."</option>"; ?></select>
<input name="valor" placeholder="Valor do frete" type="number" step="0.01" required>
<input name="prazo" placeholder="Prazo - ex: 3 dias" required>
<button name="gerar_frete">Gerar Frete</button></form>
<h3>Fretes gerados:</h3>
<?php foreach($fretes as $f) echo "✅ ".($f['data_frete'] ?? '')." | Produto: <b>".$f['produto_nome']."</b> | Cliente: <b>".$f['cliente_nome']." - ".$f['cidade']."</b> | Transportadora: <b>".$f['transportadora']."</b> | Valor: R$ ".$f['valor']." | Prazo: ".$f['prazo']."<br>"; ?>
</div>
</body></html>