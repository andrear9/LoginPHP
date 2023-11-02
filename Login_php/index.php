<?php
require_once './vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

ini_set('display_errors', 0);
ini_set('error_log', 'error.log');

function AccessLog(): Logger
{
    $log = new Logger('log_accesso');
    return $log->pushHandler(new StreamHandler('access.log', Logger::INFO));
}

function DbConnection(): PDO
{
    $dsn = 'mysql:dbname=Login_test;host=127.0.0.1';
    $user = 'root';
    $password = '';
    return new PDO($dsn, $user, $password);
}

function DbCredentials($pdo, $emailpost){
    $sql = 'SELECT username, email, passw
        FROM utenti
        WHERE email = :email';

    $sth = $pdo->prepare($sql);

    $sth->bindValue(':email', $emailpost);
    $sth->execute();
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

function Login() :void {
    session_start();

    if (isset($_SESSION['user'])) {
        header('Location: home.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $emailpost = $_POST['email'];
        $passwpost = $_POST['password'];

        if (!empty($emailpost) && !empty($passwpost)) {

            if (filter_var($emailpost, FILTER_VALIDATE_EMAIL)) {
                try {
                    $pdo = DbConnection();
                    $result = DbCredentials($pdo, $emailpost);

                    $emaildb = $result[0]["email"];
                    $passwdb = $result[0]["passw"];
                    $username = $result[0]["username"];

                    if ($emailpost === $emaildb && $passwpost === $passwdb) {
                        $_SESSION['user'] = $emailpost;
                        AccessLog()->info('Accesso eseguito da: ' . $username . " - email: " . $emailpost);
                        header('Location: home.php');
                        exit();
                    } else {
                        AccessLog()->error('Errore! Accesso non riuscito, utente: ' . $emailpost);
                        echo "Credenziali errate, riprova!";
                    }

                } catch (Exception $e) {
                    printf("Connessione fallita: %s \n", $e->getMessage());
                    exit(1);
                }
            } else {
                echo "L'indirizzo email non è valido!";
            }
        } else {
            echo "Devi inserire i dati richiesti per accedere!";
        }
    }
}

Login();
?>

<!DOCTYPE html>
<html lang="ita">
<head>
    <title>Login</title>
</head>
<body>

<h1>Login</h1>

<form action="index.php" method="post">
    <label for="email">Inserisci l'email: </label>
    <input type="text" name="email"><br><br>

    <label for="password">Inserisci la password:</label>
    <input type="password" name="password"><br><br>

    <input type="submit" value="Invia">
</form>
</body>
</html>
