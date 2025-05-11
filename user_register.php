<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            color: #d62828;
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: #444;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .radio-group,
        .checkbox-group {
            margin-top: 10px;
        }

        .radio-group label,
        .checkbox-group label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #e63946;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #c1272d;
        }

        .note {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
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
    <h2>🩸 User Registration</h2>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Age:</label>
        <input type="number" name="age" required>

        <label>Contact Number:</label>
        <input type="text" name="contract_number" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Blood Type:</label>
        <input type="text" name="blood_type" required>

        <label>Last Donation Date:</label>
        <input type="date" name="last_donation_date">

        <label>Do you have any health issues?</label>
        <div class="radio-group">
            <label><input type="radio" name="health_issues" value="1" required> Yes</label>
            <label><input type="radio" name="health_issues" value="0"> No</label>
        </div>

        <label>Health Issues Details:</label>
        <input type="text" name="health_issues_details">

        <label>Registering as:</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="is_donor" value="1" checked> Donor</label>
            <label><input type="checkbox" name="is_patient" value="1"> Patient</label>
        </div>

        <input type="submit" value="Register">
    </form>
    <div class="note">
        Already have an account? <a href="user_login.php">Login here</a>
    </div>
</div>
</body>
</html>
