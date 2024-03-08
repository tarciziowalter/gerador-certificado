<?php


use Src\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "vendor/autoload.php";
    
if(isset($_GET['senha']) && $_GET['senha'] == "47984224363"){

    $database = new Database();
    $inscricoes = $database->getInscricoesByEventoId(2, 5);
    
    $evento = $database->getByEventoId(2);
    
    foreach($inscricoes as $inscricao){
        
        $pdf = new FPDF();
        $pdf->AddPage('L');
        $pdf->Image('assets/img/certificate.png', 0, 0, 297, 210);
        $pdf->SetFont('Arial', 'B', 28); 
        
        $width = $pdf->GetStringWidth(trim($inscricao["nome"]));
        
        $pdf->SetX(297 - $width);

        $posicaoX = (297 - $width) / 2;

        $pdf->SetXY($posicaoX, 86);
        $pdf->SetTextColor(0,0,0);
        
        $pdf->Cell($width, 10, iconv('UTF-8', 'windows-1252', valida_nome(trim($inscricao["nome"]))), 0, 1, 'C');
        
        // Gerar certificado para cada inscrição
        $pdfPath = 'uploads/' . $inscricao['id'] . '_certificado.pdf';
        $pdf->Output('F', $pdfPath);
     
        // Enviar e-mail com certificado anexado
        enviarEmail($evento, $inscricao, $pdfPath);
    
        // Marcar certificado como emitido
        $database->emitirCertificado($inscricao["id"], 1);
    
    }
    
    if(count($inscricoes) > 0){
        echo json_encode(["msg" => "Certificados enviados com sucesso"]);
        die;
    }else{
        echo json_encode(["msg" => "Nenhum certificado para emitir"]);
        die;
    }
    

}else{
    echo json_encode(["msg" => "Acesso Inválido"]);
    die;
}

?>

<?php

// Função para enviar e-mail com PHPMailer
function enviarEmail($evento, $destinatario, $anexoPath)
{
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet    = 'UTF-8';
        $mail->isSMTP();        
        $mail->Host       = '';
        $mail->SMTPAuth   = true;
        $mail->Username   = '';
        $mail->Password   = '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('webmaster@adsaofrancisco.com.br', 'AD São Francisco do Sul');
        $mail->addAddress($destinatario["email"]);

        $mail->isHTML(true);
        $mail->Subject = "Certificado de Participação - {$evento['titulo']}";
        $mail->Body = "
        <p>Olá {$destinatario['nome']},</p>
        <p>Agradecemos por sua participação no {$evento['titulo']}. Anexamos o seu certificado de participação.</p>
        <p>Atenciosamente,<br>Ministério de Louvor - AD São Francisco do Sul</p>
    ";

        // Anexar o certificado ao e-mail
        $mail->addAttachment($anexoPath, 'Certificado.pdf');

        $mail->send();

        // Limpar buffer de saída para o próximo e-mail
        ob_clean();

    } catch (Exception $e) {
        echo "Erro no envio do e-mail: {$mail->ErrorInfo}";
    }
}

function valida_nome ($Nome){
	// Calcula a quantidade de caracteres do nome
	$quantidade = strlen($Nome);
	//Variavel para fazer a comparacao se passou da quantidade maxima permitida
	$maximo_caracter = 20;
	// if para fazer a comparação e decidir se é necessario fazer o tratamento do nome
	if($quantidade<$maximo_caracter){
		return $Nome;
	}

	$Nome = explode(" ", $Nome); // cria o array $nome com as partes da string
	$num = count($Nome); // conta quantas partes o nome tem
	$novo_nome = '';
	// variavel que irá concatenar as partes do nome
	$espacos = " ";

	//Variaveis para controle qual sobrenome o foreach está 
	$count = 1;
	foreach($Nome as $var) { // loop no array
		//echo "<br/> Num ".$num."Count ".$count;
		if (($count == 1) || ($count == $num)) {
			$novo_nome .= $var.' '; // Atribui o primeiro nome
			//$count++;
		}


		//Quando for para segunda posição do array, que é o primeiro sobrenome e que não 
		//seja maior do que a quantidade de sobrenome do nome
		
		if(($count >= 2) && ($count < $num)) {
			// Quando aparecer um desses entao nao atribui
			$array = array('do', 'Do', 'DO', 'da', 'Da', 'DA', 'de', 'De', 'DE', 'dos', 'Dos', 'DOS', 'das', 'Das', 'DAS');
			//Compara se a variavel var do foreach tem algum dos conteudos nao permitos
			//do array
			if(in_array($var, $array)) {
				// não Atribui para o nome novo
			}else {
				$novo_nome .= substr($var, 0, 1).'. '; // abreviou
			} // fim 
		}
		
	$count++;

	}//Final do Foreach
	return $novo_nome;

}
