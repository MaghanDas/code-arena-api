<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class CodeExecutor
{
    public function run(string $code, string $language, string $input, int $timeLimitSeconds = 10): array
    {
        $tmpDir   = sys_get_temp_dir();
        $id       = uniqid('ca_', true);
        $inputFile = "$tmpDir/{$id}.txt";

        file_put_contents($inputFile, $input);

        [$file, $command] = match ($language) {
            'python'     => [
                "$tmpDir/{$id}.py",
                PHP_OS_FAMILY === 'Windows'
                    ? ['py', '-3', "$tmpDir/{$id}.py"]
                    : ['python3', "$tmpDir/{$id}.py"]
            ],
            'javascript' => [
                "$tmpDir/{$id}.js",
                ['node', "$tmpDir/{$id}.js"]
            ],
            'php'        => [
                "$tmpDir/{$id}.php",
                ['php', "$tmpDir/{$id}.php"]
            ],
            default => throw new \InvalidArgumentException("Unsupported language: $language")
        };

        $source = $language === 'php' && ! str_starts_with(ltrim($code), '<?php')
            ? "<?php\n".$code
            : $code;

        file_put_contents($file, $source);

        $startTime = microtime(true);
        $process   = new Process($command);
        $process->setInput($input);
        $process->setTimeout($timeLimitSeconds);

        try {
            $process->run();
            $runtimeMs = (int) ((microtime(true) - $startTime) * 1000);
        } catch (ProcessTimedOutException $e) {
            $runtimeMs = (int) ((microtime(true) - $startTime) * 1000);

            @unlink($file);
            @unlink($inputFile);

            return ['status' => 'tle', 'output' => '', 'runtime_ms' => $runtimeMs, 'error' => null];
        }

        // Clean up temp files
        @unlink($file);
        @unlink($inputFile);

        $outputStr = trim($process->getOutput() . $process->getErrorOutput());

        if (! $process->isSuccessful()) {
            return ['status' => 'error', 'output' => '', 'runtime_ms' => $runtimeMs, 'error' => $outputStr];
        }

        return ['status' => 'ok', 'output' => $outputStr, 'runtime_ms' => $runtimeMs, 'error' => null];
    }
}