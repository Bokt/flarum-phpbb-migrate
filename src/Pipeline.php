<?php

namespace Bokt\Phpbb;

use Bokt\Phpbb\Models\Model;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Eloquent\Builder;

class Pipeline
{
    protected static $types = [
        'group' => Models\Group::class,
        'user' => Models\User::class,
        'forum' => Models\Forum::class,
        'topic' => Models\Topic::class,
        'topic-state' => Models\TopicsTrack::class,
        'topic-watch' => Models\TopicsWatch::class,
    ];

    /** @var OutputStyle|null */
    protected $output;

    public function types(): array
    {
        return static::$types;
    }

    public function run()
    {
        foreach ($this->types() as $type => $class) {
            $time = time();

            $this->comment("Starting migration for $type.");

            $query = forward_static_call([$class, 'query']);

            $this->dispatch($query);

            $duration = time() - $time;

            $this->comment("Migration finished for $type, duration $duration seconds.");
        }
    }


    protected function dispatch(Builder $query)
    {
        $query->each(function (Model $model) {
            $model->import();
        });
    }

    public function setOutputInterface(OutputStyle $output)
    {
        $this->output = $output;

        return $this;
    }

    protected function comment(string $comment)
    {
        if ($this->output) {
            $this->output->comment($comment);
        }
    }
}
