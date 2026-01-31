<?php


namespace App\Translation;


use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ReflectsClosures;

class PlaceholderReplacer
{
    use ReflectsClosures;

    /**
     * The custom rendering callbacks for stringable objects.
     *
     * @var array
     */
    protected $stringableHandlers = [];

    /**
     * Make the place-holder replacements on a line.
     */
    public function makeReplacements(string $line, array $replace): string
    {
        if (empty($replace)) {
            return $line;
        }

        $shouldReplace = [];

        foreach ($replace as $key => $value) {
            if ($value instanceof Closure) {
                $line = preg_replace_callback(
                    '/<'.$key.'>(.*?)<\/'.$key.'>/',
                    fn ($args) => $value($args[1]),
                    $line
                );

                continue;
            }

            if (is_object($value) && isset($this->stringableHandlers[get_class($value)])) {
                $value = call_user_func($this->stringableHandlers[get_class($value)], $value);
            }

            $shouldReplace[':'.Str::ucfirst($key)] = Str::ucfirst($value ?? '');
            $shouldReplace[':'.Str::upper($key)] = Str::upper($value ?? '');
            $shouldReplace[':'.$key] = $value;
        }

        return strtr($line, $shouldReplace);
    }

    /**
     * Add a handler to be executed in order to format a given class to a string during translation replacements.
     *
     * @param  callable|string  $class
     * @param  callable|null  $handler
     * @return void
     */
    public function stringable($class, $handler = null): void
    {
        if ($class instanceof Closure) {
            [$class, $handler] = [
                $this->firstClosureParameterType($class),
                $class,
            ];
        }

        $this->stringableHandlers[$class] = $handler;
    }
}
