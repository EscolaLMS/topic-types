<?php

namespace EscolaLms\TopicTypes\Services\Contracts;

interface TopicTypeServiceContract
{
    public function fixAssetPaths(): array;

    public function fixTopicTypeColumnName(): int;
}
