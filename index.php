<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Donation Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 60px;
            background-color: #fff5f5;
            margin: 0;
        }

        .container {
            display: inline-block;
            padding: 40px 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #d62828;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .btn {
            display: block;
            width: 220px;
            padding: 12px;
            margin: 12px auto;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            background-color: #e63946;
            color: white;
            border: none;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #c1272d;
        }

        .link {
            margin-top: 25px;
            font-size: 15px;
        }

        a {
            color: #e63946;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>🩸 Welcome to the Blood Donation Management System</h2>

        <form action="user_login.php" method="get">
            <button class="btn" type="submit">Login as User</button>
        </form>

        <form action="staff_login.php" method="get">
            <button class="btn" type="submit">Login as Staff</button>
        </form>

        <div class="link">
            <p>New donor? <a href="user_register.php">Register here</a></p>
        </div>
    </div>

</body>
</html>
