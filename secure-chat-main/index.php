<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/4733528720.js" crossorigin="anonymous"></script>

    <title>Secured Chat App</title>
    <link rel="stylesheet" type="text/css" href="resources/css/index.css">
    <link rel="stylesheet" type="text/css" href="resources/css/queries.css">
    <link rel="stylesheet" type="text/css" href="vendors/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="vendors/css/normalize.css">
    <link rel="stylesheet" type="text/css" href="vendors/css/Grid.css">
</head>

<style>
    .responsive-image {
        width: 30%;
    }

    @media only screen and (max-width: 600px) {
        .responsive-image {
            width: 90%;
        }
    }
</style>

<body style="background: linear-gradient(45deg, #070827, #211F44, #302E56)!important;height:100vh;font-family:'Poppins';display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;">
    <div class="container">
        <h1 style="text-align: center; font-size:54px;color:white;font-weight:bold">
            Login
        </h1>
        <div style="text-align-last:center;">
            <img src="assets/images/security.png" class="responsive-image" />
        </div>
        <form action="" method="POST"
            style="margin: 20px auto;box-shadow: 1px 1px 10px rgb(16 15 38);border:none;background:white;">
            <div class="form-group p-3">
                <input type="text" class="form-control" placeholder="Username" required name="username"
                    style="background-color:#e9ecef!important">
            </div>

            <div class="input-group mb-3 p-3">
                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password"
                    name="password" required style="background-color:#e9ecef!important">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-secondary w-100" style="background-color:#211F44"
                        name="login">Login</button>
                </div>
            </div>
        </form>
        <div id="small" style="text-align: center;">
            <small>
                <h4 style="text-align: center; font-size:20px;color:white">
                    Don't have account?
                </h4>
                <a href="register.php" class="btn btn-secondary" style="border-radius:40px;width:10rem">Register?</a>
            </small>
        </div>
        <?php require_once 'server/server.php'; ?>
    </div>
</body>

</html>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>