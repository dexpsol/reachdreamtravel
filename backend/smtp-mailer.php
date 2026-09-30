<?php
function smtp_read_reply($socket): array
{
    $reply = '';
    do {
        $line = fgets($socket, 8192);
        if ($line === false) throw new RuntimeException('SMTP connection ended unexpectedly.');
        $reply .= $line;
    } while (isset($line[3]) && $line[3] === '-');

    return [(int) substr($reply, 0, 3), trim($reply)];
}

function smtp_command($socket, string $command, array $accepted): string
{
    if (fwrite($socket, $command . "\r\n") === false) throw new RuntimeException('SMTP write failed.');
    [$code, $reply] = smtp_read_reply($socket);
    if (!in_array($code, $accepted, true)) throw new RuntimeException('SMTP server rejected a command.');
    return $reply;
}

function smtp_send_message(array $config, string $recipient, string $subject, string $body, ?string $replyTo = null): bool
{
    $host = $config['smtp_host'];
    $context = stream_context_create(['ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
        'peer_name' => $host,
    ]]);
    $socket = @stream_socket_client(
        'ssl://' . $host . ':' . $config['smtp_port'],
        $errorCode,
        $errorMessage,
        20,
        STREAM_CLIENT_CONNECT,
        $context
    );
    if (!$socket) throw new RuntimeException('Could not connect to the SMTP server.');
    stream_set_timeout($socket, 20);

    try {
        [$code] = smtp_read_reply($socket);
        if ($code !== 220) throw new RuntimeException('SMTP server is unavailable.');
        $hostname = preg_replace('/[^a-zA-Z0-9.-]/', '', (string) gethostname()) ?: 'localhost';
        smtp_command($socket, 'EHLO ' . $hostname, [250]);
        smtp_command($socket, 'AUTH LOGIN', [334]);
        smtp_command($socket, base64_encode($config['smtp_user']), [334]);
        smtp_command($socket, base64_encode($config['smtp_password']), [235]);
        smtp_command($socket, 'MAIL FROM:<' . $config['from'] . '>', [250]);
        smtp_command($socket, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        smtp_command($socket, 'DATA', [354]);

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $config['from_name'] . ' <' . $config['from'] . '>',
            'To: <' . $recipient . '>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
        ];
        if ($replyTo !== null) $headers[] = 'Reply-To: ' . $replyTo;
        $message = implode("\r\n", $headers) . "\r\n\r\n" . quoted_printable_encode(str_replace(["\r\n", "\r"], "\n", $body));
        $message = preg_replace('/(?m)^\./', '..', $message);
        if (fwrite($socket, str_replace("\n", "\r\n", $message) . "\r\n.\r\n") === false) {
            throw new RuntimeException('Could not send the email content.');
        }
        [$code] = smtp_read_reply($socket);
        if ($code !== 250) throw new RuntimeException('SMTP server did not accept the email.');
        smtp_command($socket, 'QUIT', [221]);
        return true;
    } finally {
        fclose($socket);
    }
}
