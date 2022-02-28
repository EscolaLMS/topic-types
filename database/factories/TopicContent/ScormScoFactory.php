<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent;

use EscolaLms\Scorm\Services\Contracts\ScormServiceContract;
use EscolaLms\TopicTypes\Models\TopicContent\ScormSco;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Peopleaps\Scorm\Model\ScormScoModel;

class ScormScoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ScormSco::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $scormSco = ScormScoModel::inRandomOrder()->first();
        return [
            'value' => isset($scormSco) ? $scormSco->id : $this->getScormSco()->id,
        ];
    }

    private function createScorm()
    {
        $mockPath = __DIR__ . '/../../mocks/scorm.zip';
        $tmpPath = __DIR__ . '/../../mocks/tmp.zip';
        copy($mockPath, $tmpPath);

        $file =  new UploadedFile($tmpPath, basename($mockPath), 'application/zip', null, true);

        try {
            $scormService = app()->make(ScormServiceContract::class);
            return $scormService->uploadScormArchive($file);
        } catch (\Exception $err) {
            echo $err->getMessage();
        } finally {
            if (is_file($tmpPath)) {
                unlink($tmpPath);
            }
        }

        return null;
    }

    private function getScormSco(): ScormScoModel
    {
        $scorm = $this->createScorm();

        return $scorm['model']->scos->first();
    }
}
