<?php

namespace EscolaLms\TopicTypes\Tests;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Tests\Models\Client;
use EscolaLms\Categories\EscolaLmsCategoriesServiceProvider;
use EscolaLms\Courses\AuthServiceProvider;
use EscolaLms\Courses\EscolaLmsCourseServiceProvider;
use EscolaLms\Courses\Tests\Models\User as UserTest;
use EscolaLms\HeadlessH5P\HeadlessH5PServiceProvider;
use EscolaLms\ModelFields\ModelFieldsServiceProvider;
use EscolaLms\Scorm\EscolaLmsScormServiceProvider;
use EscolaLms\Tags\EscolaLmsTagsServiceProvider;
use EscolaLms\TopicTypes\EscolaLmsTopicTypesServiceProvider;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider as FFMpegServiceProvider;

class TestCase extends \EscolaLms\Courses\Tests\TestCase
{
    protected ?TestResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        Passport::useClientModel(Client::class);
    }

    protected function getPackageProviders($app)
    {
        $providers = [
            ...parent::getPackageProviders($app),
            EscolaLmsAuthServiceProvider::class,
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            EscolaLmsCategoriesServiceProvider::class,
            EscolaLmsCourseServiceProvider::class,
            AuthServiceProvider::class,
            EscolaLmsScormServiceProvider::class,
            EscolaLmsTagsServiceProvider::class,
            HeadlessH5PServiceProvider::class,
            EscolaLmsTopicTypesServiceProvider::class,
            ModelFieldsServiceProvider::class,
            FFMpegServiceProvider::class,
        ];
        if (class_exists(\EscolaLms\CoursesImportExport\EscolaLmsCoursesImportExportServiceProvider::class)) {
            $providers[] = \EscolaLms\CoursesImportExport\EscolaLmsCoursesImportExportServiceProvider::class;
        }
        if (class_exists(\EscolaLms\Cmi5\EscolaLmsCmi5ServiceProvider::class)) {
            $providers[] = \EscolaLms\Cmi5\EscolaLmsCmi5ServiceProvider::class;
        }
        return $providers;
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', UserTest::class);
        $app['config']->set('passport.client_uuids', true);
        $app['config']->set('database.connections.mysql.strict', false);
        $app['config']->set('app.debug', (bool) env('APP_DEBUG', true));
        $app['config']->set('escolalms.tags.ignore_migrations', false);
        $app['config']->set('hh5p.h5p_export', true);

        $app['config']->set('scorm', [
            'table_names' => [
                'user_table' => 'users',
                'scorm_table' => 'scorm',
                'scorm_sco_table' => 'scorm_sco',
                'scorm_sco_tracking_table' => 'scorm_sco_tracking',
            ],
            // Scorm directory. You may create a custom path in file system
            'disk' => 'local',
        ]);
    }

    public function assertApiResponse(array $actualData)
    {
        $this->assertApiSuccess();

        $response = json_decode($this->response->getContent(), true);
        $responseData = $response['data'];

        $this->assertNotEmpty($responseData['id']);
        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess()
    {
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(array $actualData, array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}
