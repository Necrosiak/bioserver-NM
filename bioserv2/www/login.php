<meta name="generator" content="Namo WebEditor(Trial)">
<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	include('header.php');
?>

<font size="-2"><br></br></font>			
<table align="center" width="100%" cellspacing="0" cellpadding="0">
    <tr align="center" valign="top">
        <td align="center" width="50%">
            <form method="post" action="login_form.php">
                <p>Connexion avec un compte existant:<br></br>
</p>
                <table>
                    <tr>
                        <td>ID:</td>
                        <td><input type="text" name="username"></input></td>
                    </tr>
					
                    <tr>
                        <td>Mot de passe:</td>
                        <td><input type="password" name="password"></input></td>
                    </tr>
					
                    <input type="hidden" name="login" value="manual"></input>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="CONNEXION"></input></td>
                    </tr>
					
                </table>
            </form>
        </td>
        <td align="center" width="50%">
            <form method="post" action="login_form.php">
                <p>Connexion avec un compte existant:<br></br>
</p>
                <table>
                    <tr>
                        <td>ID:</td>
                        <td><input type="text" name="username"></input></td>
                    </tr>
					
                    <tr>
                        <td>Mot de passe:</td>
                        <td><input type="password" name="password"></input></td>
                    </tr>
					
                    <input type="hidden" name="login" value="newaccount"></input>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="CONNEXION"></input></td>
                    </tr>
					
                </table>
            </form>
        </td>
    </tr>
</table>

<?php include('footer.php'); ?>