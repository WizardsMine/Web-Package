<?php

namespace Wizard\Src\Modules\Logger;

class Logger
{
    public static function log(string $message = '')
    {
        debug_backtrace();
    }
}

/**
 * Old debugger
 */
//try {
//    $this->checkMessage($message);
//    $this->setFilesystem();
//    if (is_string($root) && $root) {
//        if ($this->filesystem->exists($root.'/logs/debug.log')) {
//            $debug_file = file_get_contents($root.'/logs/debug.log');
//            $time = '['.date("Y-m-d] [G:i:s",time()).']: ';
//            $completed_message =  $debug_file.PHP_EOL.
//                $time.PHP_EOL.
//                '       File location: '.$file.PHP_EOL.
//                '       Line: '.$line.PHP_EOL.
//                '       Message: '.$message;
//            // Log the message
//            $this->filesystem->dumpFile($root.'/logs/debug.log', $completed_message);
//        } else {
//            // Make debug.log
//            throw new DebuggerException('debug.log file not found in logs directory');
//        }
//    } else {
//        throw new DebuggerException('Please specify a valid root directory');
//    }
//} catch (DebuggerException $e) {
//    throw new WizardRuntimeException($e->compileToArray());
//} catch (IOException $e) {
//    throw new WizardRuntimeException($e->compileToArray($e->getPath()));
//}