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
            background-color: #f9f9f9;
        }

        .container {
            display: inline-block;
            padding: 30px;
            border: 1px solid #ccc;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        .btn {
            display: block;
            width: 200px;
            padding: 10px;
            margin: 10px auto;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .link {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to Blood Donation Management System</h2>
        
        <form action="user_login.php" method="get">
            <button class="btn" type="submit">Login as User</button>
        </form>

        <form action="staff_login.php" method="get">
            <button class="btn" type="submit">Login as Staff</button>
        </form>

        <div class="link">
            <p>New user? <a href="user_register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
