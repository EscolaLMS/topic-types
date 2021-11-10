<?php

namespace EscolaLms\TopicTypes;

use EscolaLms\Courses\Http\Resources\TopicAdminResource;
use EscolaLms\Courses\Http\Resources\TopicResource;
use EscolaLms\Courses\Repositories\TopicRepository;
use EscolaLms\TopicTypes\Commands\FixAssetPathsCommand;
use EscolaLms\TopicTypes\Commands\FixTopicTypeColumnName;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\AudioResource as AdminAudioResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\H5PResource as AdminH5PResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\ImageResource as AdminImageResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\OEmbedResource as AdminOEmbedResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\PDFResource as AdminPDFResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\RichTextResource as AdminRichTextResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Admin\VideoResource as AdminVideoResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\AudioResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\H5PResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\ImageResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\OEmbedResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\PDFResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\RichTextResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\VideoResource;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\PDF;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Support\ServiceProvider;

class EscolaLmsTopicTypesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                FixTopicTypeColumnName::class,
                FixAssetPathsCommand::class,
        ]);
        }
    }

    public function register()
    {
        TopicRepository::registerContentClass(Audio::class);
        TopicRepository::registerContentClass(Video::class);
        TopicRepository::registerContentClass(Image::class);
        TopicRepository::registerContentClass(RichText::class);
        TopicRepository::registerContentClass(H5P::class);
        TopicRepository::registerContentClass(OEmbed::class);
        TopicRepository::registerContentClass(PDF::class);

        TopicResource::registerContentClass(Audio::class, AudioResource::class);
        TopicResource::registerContentClass(H5P::class, H5PResource::class);
        TopicResource::registerContentClass(Image::class, ImageResource::class);
        TopicResource::registerContentClass(OEmbed::class, OEmbedResource::class);
        TopicResource::registerContentClass(PDF::class, PDFResource::class);
        TopicResource::registerContentClass(RichText::class, RichTextResource::class);
        TopicResource::registerContentClass(Video::class, VideoResource::class);

        TopicAdminResource::registerContentClass(Audio::class, AdminAudioResource::class);
        TopicAdminResource::registerContentClass(H5P::class, AdminH5PResource::class);
        TopicAdminResource::registerContentClass(Image::class, AdminImageResource::class);
        TopicAdminResource::registerContentClass(OEmbed::class, AdminOEmbedResource::class);
        TopicAdminResource::registerContentClass(PDF::class, AdminPDFResource::class);
        TopicAdminResource::registerContentClass(RichText::class, AdminRichTextResource::class);
        TopicAdminResource::registerContentClass(Video::class, AdminVideoResource::class);
    }
}
