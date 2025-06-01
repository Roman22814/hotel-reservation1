
<?php
// hotel.php

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'hotel_reservation';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$page = $_GET['page'] ?? 'reserve';

function header_html($title) {
    echo "<!DOCTYPE html><html><head><title>$title</title><style>
        body { font-family: Arial; padding: 30px; }
        input, select { margin: 5px 0; padding: 5px; width: 250px; }
        .btn { padding: 7px 15px; margin-top: 10px; }
        .cancel-btn { background: red; color: white; padding: 5px 10px; text-decoration: none; }
    </style></head><body>";
}

function footer_html() {
    echo "</body></html>";
}

session_start();

if ($page == 'reserve') {
    header_html("Hotel Room Reservation");
    echo "<h2>Hotel Room Reservation</h2>
    <form action='?page=payment' method='post'>
        Full Name: <br><input type='text' name='name' required><br>
        Email: <br><input type='email' name='email' required><br>
        Check-in Date: <br><input type='date' name='checkin' required><br>
        Check-out Date: <br><input type='date' name='checkout' required><br>
        Room Type: <br>
        <select name='room_type'>
            <option value='Single'>Single</option>
            <option value='Double' selected>Double</option>
        </select><br>
        <input type='submit' class='btn' value='Reserve'>
    </form>";
    echo "<p><small>Fig 5.2.1: Reservation Page</small></p>";
    footer_html();
}
elseif ($page == 'payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['booking'] = $_POST;
    $res_id = rand(10, 99);

    header_html("Payment");
    echo "<h2>Payment</h2>
        <p>Click the button below to simulate payment for reservation ID: <strong>$res_id</strong></p>
        <form action='?page=confirm' method='post'>
            <input type='submit' class='btn' value='Make Payment'>
        </form>";
    echo "<p><small>Fig 5.2.2: Payment Process</small></p>";
    footer_html();
}
elseif ($page == 'confirm' && isset($_SESSION['booking'])) {
    $data = $_SESSION['booking'];
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $checkin = $data['checkin'];
    $checkout = $data['checkout'];
    $room_type = $data['room_type'];

    $user_check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($user_check->num_rows > 0) {
        $user = $user_check->fetch_assoc();
        $user_id = $user['id'];
    } else {
        $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', 'dummy')");
        $user_id = $conn->insert_id;
    }

    $days = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);
    if ($days <= 0) die("Invalid check-in/check-out");
    $price = 1000;
    $total = $price * $days;

    $conn->query("INSERT INTO bookings (user_id, room_id, check_in, check_out, total_price)
                  VALUES ($user_id, 1, '$checkin', '$checkout', $total)");

    header_html("Reservation Confirmation");
    echo "<h2>Reservation Confirmation</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Room Type:</strong> $room_type</p>
        <p><strong>Check-in:</strong> $checkin</p>
        <p><strong>Check-out:</strong> $checkout</p>
        <p><strong>Payment Status:</strong> <span style='color:green'>Paid</span></p>
        <a href='?page=cancel' class='cancel-btn'>Cancel Reservation</a>";
    echo "<p><small>Fig 5.2.3: Confirmation Page</small></p>";
    footer_html();
}
elseif ($page == 'cancel') {
    header_html("Cancel");
    echo "<h2>Your reservation has been cancelled.</h2>";
    session_destroy();
    echo "<a class='btn' href='?page=reserve'>Make a new reservation</a>";
    footer_html();
}
?>
