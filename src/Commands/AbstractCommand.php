<?php

namespace Commands;

use Exception;
use Helpers\DateOperator;

abstract class AbstractCommand implements Command {
    protected ?string $value;
    protected array $argsMap = [];
    protected static ?string $alias = null;
    protected static bool $requiredCommandValue = false;

    /**
     * @throws Exception
     */
    public function __construct(){
        $this->setUpArgsMap();
    }

    /*
     * シェルからすべての引数を読み込み、ハッシュマップを作成
     * このargsMapは getArgumentValue()のために使用
     * すべての引数は短縮バージョンでは"-"で、完全なバージョンでは"--"で始まる
     */
    private function setUpArgsMap(): void {
        $args = $GLOBALS["argv"];
        // エイリアスのインデックスが見つかるまで探索
        $startIndex  = array_search($this->getAlias(), $args);

        if ($startIndex === false) throw new Exception(sprintf("Could not find alias %s", $this->getAlias()));
        else $startIndex++;

        $shellArgs = [];

        // メインコマンドの値が必須である場合、その値を取得
        if (!isset($args[$startIndex]) || ($args[$startIndex][0] === "-")) {
            if ($this->isCommandValueRequired()) throw new Exception(sprintf("%s's value is required.", $this->getAlias()));
        } else {
            $this->argsMap[$this->getAlias()] = $args[$startIndex];
            $startIndex++;
        }

        // すべての引数を$shellArgsハッシュに格納
        for ($i = $startIndex; $i < count($args); $i++) {
            $arg = $args[$i];

            if ($arg[0].$arg[1] === "--") $key = substr($arg, 2);
            else if ($arg[0] === "-") $key = substr($arg, 1);
            else throw new Exception("Options must start with - or --");

            $shellArgs[$key] = true;

            // 次のargsエントリがオプションでない場合は、引数値となる
            if (isset($args[$i+1]) && $args[$i+1] !== "-") {
                $shellArgs[$key] = $args[$i+1];
                $i++;
            }
        }

        // このコマンドの引数マップを設定
        foreach ($this->getArguments() as $argument) {
            $argString = $argument->getArgument();
            $value = null;

            if ($argument->isShortAllowed() && isset($shellArgs[$argString[0]])) $value = $shellArgs[$argString[0]];
            else if (isset($shellArgs[$argString])) $value = $shellArgs[$argString];

            if ($value === null) {
                if ($argument->isRequired()) throw new Exception(sprintf("Could not find the required argument %s", $argString));
                else $this->argsMap[$argString] = false;
            }
            else $this->argsMap[$argString] = $value;
        }

        $this->log(json_encode($this->argsMap));
    }

    public static function getHelp(): string {
        $helpString = "Command: " . static::getAlias() . (static::isCommandValueRequired() ? " {value}" : "") . PHP_EOL;

        $arguments = static::getArguments();
        if (empty($arguments)) return $helpString;

        $helpString .= "Arguments:" . PHP_EOL;

        foreach ($arguments as $argument) {
            $helpString .= "  --" . $argument->getArgument();  // long argument name
            if ($argument->isShortAllowed()) {
                $helpString .= " (-" . $argument->getArgument()[0] . ")";  // short argument name
            }
            $helpString .= ": " . $argument->getDescription();
            $helpString .= $argument->isRequired() ? " (Required)" : "(Optional)";
            $helpString .= PHP_EOL;
        }

        return $helpString;
    }

    public static function getAlias(): string {
        return static::$alias !== null ? static::$alias : static::class;
    }

    public static function isCommandValueRequired(): bool {
        return static::$requiredCommandValue;
    }

    public function getCommandValue(): string {
        return $this->argsMap[static::getAlias()] ?? "";
    }

    /**
     * 引数の値が設定されている場合はその値を、
     * 引数は存在するが値が設定されていない場合はtrue、
     * 引数が存在しない場合はfalseを返す
     */
    public function getArgumentValue(string $arg): bool | string {
        return $this->argsMap[$arg];
    }

    protected function log(string $info): void {
        $timestamp = DateOperator::formatDateTime(DateOperator::getCurrentDateTime());
        fwrite(STDOUT, "[$timestamp] $info" . PHP_EOL);
    }

    /** @return Argument[]  */
    public abstract static function getArguments(): array;
    public abstract function execute(): int;
}
