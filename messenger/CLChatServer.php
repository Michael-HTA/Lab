<?php

/**
 * This program is one end of a simple command-line interface chat program.
 * It acts as a server which waits for a connection from the CLChatClient 
 * program.  The port on which the server listens can be specified as a 
 * command-line argument.  If it is not, then the port specified by the
 * constant DEFAULT_PORT is used.  Note that if a port number of zero is 
 * specified, then the server will listen on any available port.
 * This program only supports one connection.  As soon as a connection is 
 * opened, the listening socket is closed down.  The two ends of the connection
 * each send a HANDSHAKE string to the other, so that both ends can verify
 * that the program on the other end is of the right type.  Then the connected 
 * programs alternate sending messages to each other.  The client always sends 
 * the first message.  The user on either end can close the connection by 
 * entering the string "quit" when prompted for a message.  Note that the first 
 * character of any string sent over the connection must be 0 or 1; this
 * character is interpreted as a command.
 */

// Port to listen on, if none is specified on the command line.
define('DEFAULT_PORT', 1728);

// Handshake string. Each end of the connection sends this  string to the 
// other just after the connection is opened.  This is done to confirm that 
// the program on the other side of the connection is a CLChat program.
define('HANDSHAKE', 'CLChat');

// This character is prepended to every message that is sent.
define('MESSAGE', '0');

// This character is sent to the connected program when the user quits.
define('CLOSE', '1');

$port = isset($argv[1]) ? (int)$argv[1] : DEFAULT_PORT;

$listener = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

//to solve the unable to bind problem
if (!socket_set_option($listener, SOL_SOCKET, SO_REUSEADDR, 1)) {

    echo socket_strerror(socket_last_error($sock));

    exit;

}
socket_bind($listener,'127.0.0.1',$port);
socket_listen($listener);

echo "Listening on port $port\n";

$connection = socket_accept($listener);
socket_shutdown($listener,2);

// Send handshake with newline character at the end
socket_write($connection, HANDSHAKE . "\n", strlen(HANDSHAKE) + 1);


$messageIn = trim(socket_read($connection, 1024));

if ($messageIn !== HANDSHAKE) {
    throw new Exception("Connected program is not a CLChat!");
}

echo "Connected. Waiting for the first message.\n";

$userInput = fopen('php://stdin', 'r');

echo "NOTE: Enter 'quit' to end the program.\n";

while (true) {
    echo "WAITING...\n";
    $messageIn = socket_read($connection, 1024);
    if ($messageIn) {
        if ($messageIn[0] === CLOSE) {
            echo "Connection closed at other end.\n";
            socket_close($connection);
            return;
        }
        $messageIn = substr($messageIn, 1);
    }
    echo "RECEIVED:  $messageIn\n";
    echo "SEND:      ";
    $messageOut = trim(fgets($userInput));
    if (strtolower($messageOut) === 'quit') {
        socket_write($connection, CLOSE . "\n", 2);
        socket_shutdown($connection,2);
        echo "Connection closed.\n";
        return;
    }
    socket_write($connection, MESSAGE . $messageOut . "\n", strlen(MESSAGE . $messageOut) + 1);
}

