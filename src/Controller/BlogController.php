<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    public array $navElem = [
        ['nev'=>'League of Legends','link'=>'/forum/game1'],
        ['nev'=>'Diablo IV','link'=>'/forum/game2'],
        ['nev'=>'Call of Duty','link'=>'/forum/game3'],
        ['nev'=>'Mini Motorways','link'=>'/forum/game4'],
        ['nev'=>'Minecraft','link'=>'/forum/game5'],
        ['nev'=>'Login','link'=>'/login/'],
    ];
    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        return $this->render('Twigs/homepage.html.twig', [
            'title' => 'Fő oldal címe',
            'nav' => $this -> navElem,
        ]);
    }
    #[Route('/forum/{slug}', name: 'app_forum')]
    public function forum(string $slug = null): Response
    {
        return $this->render('Twigs/forum.html.twig',[
            'title' => $slug,
            'nav' => $this -> navElem,
        ]);
    }
    #[Route('/login/', name:'app_login')]
    public function login(): Response
    {
        return $this->render('Twigs/login.html.twig',[
            'nav' => $this -> navElem,
        ]);
    }
    #[Route('/adminBase/',name:'app_adminBase')]
    public function adminBase(): Response
    {
        return $this->render('Twigs/adminBase.html.twig',[
            'nav' => $this -> navElem,
        ]);
    }
    #[Route('/adminBase/regAdmin/', name:'app_regAdmin')]
    public function regAdmin(): Response
    {
        return $this->render('Twigs/regAdmin.html.twig',[
            'nav' => $this -> navElem,
        ]);
    }

    //register
    function emptyInputSignup($name, $email, $pw1, $pw2)
    {
        $result = false;
        if (empty($name) || empty($email) || empty($pw1) || empty($pw2) ) {
            $result = true;
        }
        return $result;
    }
    function invalidName($name,$email) {
        $result=false;
        if (!preg_match("/^[a-zA-Z0-9]*$/", $name) || !preg_match("/^[a-zA-Z0-9]*$/", $email)) {
            // Email:@?
            $result = true;
        }
        return $result;
    }
    function pwdMatch($pw1, $pw2) {
        $result = false;
        if ($pw1 != $pw2) {
            $result = true;
        }
        return $result;
    }
    function uidExists($conn,$name) {
        $sql = "SELECT * FROM usertable WHERE userName = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("location: /login?=stmterror"); // átdolgozás alatt
            exit();
        }
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultData)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        else{
            mysqli_stmt_close($stmt);
            return false;
        }
    }

    function createUser($name, $email, $pw1, $conn) {

        $registerdate = date("Y.m.d. h:i:sa");
        $sql = "INSERT INTO usertable ( name, pw1, email, registerdate) VALUES ( ?, ?, ?, ?);";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("location: /login?=stmtfailed"); // átdolgozás alatt
            exit();
        }

        $hashedpwd = password_hash($pw1, PASSWORD_DEFAULT);

        mysqli_stmt_bind_param($stmt, "sssdb",$userName,$email,$hasPass,$regDate,$adminStatus);
        // formátumok? d-date b-booline
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("location: /login?"); // átdolgozás alatt
        exit();
    }

    //login
    function emptyInputLogin($username, $pw1) {
        $result=false;
        if (empty($username) || empty($pw1)) {
            $result = true;
        }
        return $result;
    }
    function loginUser($username,$pwd,$conn){
        $uidExists = uidExists($conn,$username);
        if (!$uidExists) {

            exit();
        }

        $pwdHashed = $uidExists["pwd"];
        $checkPwd = password_verify($pwd,$pwdHashed);

        if (!$checkPwd) {
            header("location: /login?error=wronglogin2");
            exit();
        }
        elseif ($checkPwd) {
            session_start();
            $_SESSION["userid"] = $uidExists["playerID"];
            $_SESSION["username"] = $uidExists["username"];
            header("location: /index.php");
            exit();
        }
    }
}