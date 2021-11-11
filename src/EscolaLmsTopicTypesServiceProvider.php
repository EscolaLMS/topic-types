<?php

namespace EscolaLms\TopicTypes;

use EscolaLms\Courses\Http\Resources\TopicExportResource;
use EscolaLms\Courses\Http\Resources\TopicResource;
use EscolaLms\Courses\Repositories\TopicRepository;
use EscolaLms\TopicTypes\Commands\FixAssetPathsCommand;
use EscolaLms\TopicTypes\Commands\FixTopicTypeColumnName;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\AudioResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\H5PResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\ImageResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\OEmbedResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\PDFResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\RichTextResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Client\VideoResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\AudioResource as ExportAudioResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\H5PResource as ExportH5PResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\ImageResource as ExportImageResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\OEmbedResource as ExportOEmbedResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\PDFResource as ExportPDFResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\RichTextResource as ExportRichTextResource;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Export\VideoResource as ExportVideoResource;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\PDF;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use EscolaLms\TopicTypes\Services\Contracts\TopicTypeServiceContract;
use EscolaLms\TopicTypes\Services\TopicTypeService;
use Illuminate\Support\ServiceProvider;

class EscolaLmsTopicTypesServiceProvider extends ServiceProvider
{
    public $singletons = [
        TopicTypeServiceContract::class => TopicTypeService::class,
    ];

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

        TopicExportResource::registerContentClass(Audio::class, ExportAudioResource::class);
        TopicExportResource::registerContentClass(H5P::class, ExportH5PResource::class);
        TopicExportResource::registerContentClass(Image::class, ExportImageResource::class);
        TopicExportResource::registerContentClass(OEmbed::class, ExportOEmbedResource::class);
        TopicExportResource::registerContentClass(PDF::class, ExportPDFResource::class);
        TopicExportResource::registerContentClass(RichText::class, ExportRichTextResource::class);
        TopicExportResource::registerContentClass(Video::class, ExportVideoResource::class);
    }
}
