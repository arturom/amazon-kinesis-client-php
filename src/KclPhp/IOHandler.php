<?php
namespace KclPhp;

class IOHandler {

    private $stdin;

    private $stdout;

    private $stderr;

    public function __construct($stdin = STDIN, $stdout = STDOUT, $stderr = STDERR) {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function readLine() {
        return json_decode(fgets($this->stdin));
    }

    public function writeMessage(array $message) {
        fwrite($this->stdout, json_encode($message));
        fwrite($this->stdout, PHP_EOL);
    }

    public function writeException(\Exception $e) {
        fwrite($this->stderr, $e->getMessage());
        fwrite($this->stderr, $e->getTraceAsString());
        fwrite($this->stderr, PHP_EOL);
    }

}
