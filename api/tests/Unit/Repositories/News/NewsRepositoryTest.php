<?php

namespace Tests\Unit\Repositories\News;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\S3Helper;
use App\Models\News;
use App\Models\NewsLanguage;
use App\Models\NewsToCategory;
use App\Repositories\News\NewsRepository;
use Faker\Factory as FakerFactory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use StdClass;
use TestCase;

class NewsRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->generateMocks();
    }

    public function testGetNewsTitle()
    {
        $newsId = 1;
        $languageId = 2;
        $defaultTenantLanguageId = 3;

        $this->newsLanguage
            ->shouldReceive('select')
            ->once()
            ->with('title', 'language_id')
            ->andReturnSelf();

        $this->newsLanguage
            ->shouldReceive('where')
            ->twice()
            ->andReturnSelf();

        $this->newsLanguage
            ->shouldReceive('get')
            ->once()
            ->andReturnSelf();

        $this->newsLanguage
            ->shouldReceive('count')
            ->once()
            ->andReturn(1);  // as long as its greater than 0

        $this->newsLanguage
            ->shouldReceive('toArray')
            ->once()
            ->andReturn([
                ['language_id' => $languageId],
            ]);

        $news = new News;
        $news->title = 'news today';
        $this->newsLanguage
            ->shouldReceive('first')
            ->once()
            ->andReturn($news);

        $this->newsLanguage
            ->shouldReceive('withTrashed')
            ->once()
            ->andReturnSelf();

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsTitle($newsId, $languageId, $defaultTenantLanguageId);

        $this->assertSame('news today', $result);
    }

    public function testGetNewsTitleNoResult()
    {
        $newsId = 1;
        $languageId = 2;
        $defaultTenantLanguageId = 3;

        $this->newsLanguage
            ->shouldReceive('select')
            ->once()
            ->with('title', 'language_id')
            ->andReturnSelf();

        $this->newsLanguage
            ->shouldReceive('where')
            ->once()
            ->with(['news_id' => $newsId])
            ->andReturnSelf();

        $this->newsLanguage
            ->shouldReceive('get')
            ->once()
            ->andReturnSelf();

        $this->newsLanguage
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);  // no entry

        $this->newsLanguage
            ->shouldReceive('toArray')
            ->never();

        $this->newsLanguage
            ->shouldReceive('first')
            ->never();

        $this->newsLanguage
            ->shouldReceive('withTrashed')
            ->once()
            ->andReturnSelf();

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsTitle($newsId, $languageId, $defaultTenantLanguageId);

        $this->assertEmpty($result);
    }

    public function testGetNewsDetails()
    {
        $newsId = 1;
        $newsStatus = config('constants.news_status.PUBLISHED');

        $this->news
            ->shouldReceive('with')
            ->twice()
            ->andReturnSelf();

        $this->news
            ->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $this->news
            ->shouldReceive('findOrFail')
            ->once()
            ->andReturn(new News);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsDetails($newsId, $newsStatus);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testGetNewsDetailsNoStatus()
    {
        $newsId = 1;
        $newsStatus = null;

        $this->news
            ->shouldReceive('with')
            ->twice(3)
            ->andReturnSelf();

        $this->news
            ->shouldNotReceive('where');

        $this->news
            ->shouldReceive('findOrFail')
            ->once()
            ->andReturn(new News);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsDetails($newsId, $newsStatus);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testGetNewsListWithSearchAndOrdering()
    {
        $this->request->query->add([
            'search' => 'search',
            'order' => true,
        ]);
        $newsStatus = config('constants.news_status.PUBLISHED');

        $this->news
            ->shouldReceive('with')
            ->twice()
            ->andReturnSelf();
        $this->news
            ->shouldReceive(
                'whereHas',
                'orderBy',
                'where',
            )
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldNotReceive('select'); // used in a callback
        $this->news
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($this->lengthAwarePaginator);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsList($this->request, $newsStatus);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testGetNewsListWithSearchOnlyNoOrdering()
    {
        $this->request->query->add([
            'search' => 'search',
        ]);
        $newsStatus = config('constants.news_status.PUBLISHED');

        $this->news
            ->shouldReceive('with')
            ->twice()
            ->andReturnSelf();
        $this->news
            ->shouldReceive(
                'where',
                'whereHas',
            )
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldNotReceive(
                'orderBy',
                'select',  // used in a callback
            );
        $this->news
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($this->lengthAwarePaginator);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsList($this->request, $newsStatus);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testGetNewsListWithoutSearchNorOrdering()
    {
        $this->request->query->add([
            'search' => 'search',
            'order' => true,
        ]);
        $newsStatus = config('constants.news_status.PUBLISHED');

        $this->news
            ->shouldReceive('with')
            ->twice()
            ->andReturnSelf();
        $this->news
            ->shouldReceive(
                'orderBy',
                'where',
                'whereHas',
            )
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldNotReceive(
                'select',  // used in a callback
            );
        $this->news
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($this->lengthAwarePaginator);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsList($this->request, $newsStatus);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testGetNewsListWithoutStatus()
    {
        $this->request->query->add([
            'search' => 'search',
            'order' => true,
        ]);
        $newsStatus = null;

        $this->news
            ->shouldReceive('with')
            ->twice()
            ->andReturnSelf();
        $this->news
            ->shouldReceive(
                'orderBy',
                'whereHas',
            )
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldNotReceive(
                'select',  // used in a callback
                'where',
            );
        $this->news
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($this->lengthAwarePaginator);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->getNewsList($this->request, $newsStatus);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testDelete()
    {
        $newsId = 1;

        $news = $this->createMock(News::class);

        $this->news
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        $this->news
            ->shouldReceive('findOrFail')
            ->once()
            ->with($newsId)
            ->andReturnSelf();

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->delete($newsId);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testStore()
    {
        $newsId = 1;
        $query = [
            'user_name' => $this->faker->userName,
            'user_title' => $this->faker->bs,
            'user_thumbnail' => $this->faker->imageUrl,
            'status' => config('constants.news_status.PUBLISHED'),
        ];
        $this->request->query->add($query);
        $this->request->merge([
            'news_image' => $this->faker->imageUrl,
            'news_content' => [
                'translations' => [
                    [
                        'lang' => 'en',
                        'title' => $this->faker->bs,
                        'description' => $this->faker->text,
                    ],
                ]
            ],
        ]);

        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldReceive('create')
            ->once();

        $this->newsToCategory
            ->shouldReceive('create')
            ->once();

        $this->news
            ->shouldReceive(
                'create',
                'update',
            )
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldReceive('getAttribute')
            ->times(3)
            ->with('news_id')  // for $news->news_id
            ->andReturn($newsId);

        $collection = $this->mockAnything(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = new StdClass;
        $language->language_id = 1;
        $collection
            ->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $collection
            ->expects($this->once())
            ->method('first')
            ->willReturn($language);
        $this->languageHelper
            ->expects($this->once())
            ->method('getLanguages')
            ->willReturn($collection);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->store($this->request);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testStoreNoNewsImage()
    {
        $newsId = 1;
        $query = [
            'user_name' => $this->faker->userName,
            'user_title' => $this->faker->bs,
            'user_thumbnail' => $this->faker->imageUrl,
            'status' => config('constants.news_status.PUBLISHED'),
        ];
        $this->request->query->add($query);
        $this->request->merge([
            'news_content' => [
                'translations' => [
                    [
                        'lang' => 'en',
                        'title' => $this->faker->bs,
                        'description' => $this->faker->text,
                    ],
                ]
            ],
        ]);

        $this->helpers
            ->expects($this->never())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->never())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldReceive('create')
            ->once();

        $this->newsToCategory
            ->shouldReceive('create')
            ->once();

        $this->news
            ->shouldReceive('update')
            ->never();
        $this->news
            ->shouldReceive('create')
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('news_id')  // for $news->news_id
            ->andReturn($newsId);

        $collection = $this->mockAnything(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = new StdClass;
        $language->language_id = 1;
        $collection
            ->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $collection
            ->expects($this->once())
            ->method('first')
            ->willReturn($language);
        $this->languageHelper
            ->expects($this->once())
            ->method('getLanguages')
            ->willReturn($collection);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->store($this->request);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testStoreNoNewsContent()
    {
        $newsId = 1;
        $query = [
            'user_name' => $this->faker->userName,
            'user_title' => $this->faker->bs,
            'user_thumbnail' => $this->faker->imageUrl,
            'status' => config('constants.news_status.PUBLISHED'),
        ];
        $this->request->query->add($query);
        $this->request->merge([
            'news_image' => $this->faker->imageUrl,
        ]);

        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldReceive('create')
            ->never();

        $this->newsToCategory
            ->shouldReceive('create')
            ->once();

        $this->news
            ->shouldReceive(
                'create',
                'update',
            )
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('news_id')  // for $news->news_id
            ->andReturn($newsId);

        $this->languageHelper
            ->expects($this->never())
            ->method('getLanguages');

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->store($this->request);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testStoreNoNewsAtAll()
    {
        $newsId = 1;
        $query = [
            'user_name' => $this->faker->userName,
            'user_title' => $this->faker->bs,
            'user_thumbnail' => $this->faker->imageUrl,
            'status' => config('constants.news_status.PUBLISHED'),
        ];
        $this->request->query->add($query);

        $this->helpers
            ->expects($this->never())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->never())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldNotReceive('create');

        $this->newsToCategory
            ->shouldReceive('create')
            ->once();

        $this->news
            ->shouldNotReceive('update');

        $this->news
            ->shouldReceive('create')
            ->once()
            ->andReturnSelf();
        $this->news
            ->shouldReceive('getAttribute')
            ->once()
            ->with('news_id')  // for $news->news_id
            ->andReturn($newsId);

        $this->languageHelper
            ->expects($this->never())
            ->method('getLanguages');

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->store($this->request);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testUpdate()
    {
        $newsId = 1;
        $query = [
            'news_category_id' => 1,
            'news_image' => $this->faker->imageUrl,
            'news_content' => [
                'translations' => [
                    [
                        'lang' => 'en',
                        'title' => $this->faker->bs,
                        'description' => $this->faker->text,
                    ],
                ]
            ],
        ];
        $this->request->query->add($query);

        $this->news
            ->shouldReceive('findOrFail')
            ->once()
            ->with($newsId)
            ->andReturnSelf();

        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldReceive('createOrUpdateNewsLanguage')
            ->once();

        $this->newsToCategory
            ->shouldReceive(
                'where',
                'update',
            )
            ->once()
            ->andReturnSelf();

        $this->news
            ->shouldReceive('update')
            ->once()
            ->andReturnSelf();

        $collection = $this->mockAnything(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = new StdClass;
        $language->language_id = 1;
        $collection
            ->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $collection
            ->expects($this->once())
            ->method('first')
            ->willReturn($language);
        $this->languageHelper
            ->expects($this->once())
            ->method('getLanguages')
            ->willReturn($collection);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->update($this->request, $newsId);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testUpdateNoNewsCategory()
    {
        $newsId = 1;
        $query = [
            'news_image' => $this->faker->imageUrl,
            'news_content' => [
                'translations' => [
                    [
                        'lang' => 'en',
                        'title' => $this->faker->bs,
                        'description' => $this->faker->text,
                    ],
                ]
            ],
        ];
        $this->request->query->add($query);

        $this->news
            ->shouldReceive('findOrFail')
            ->once()
            ->with($newsId)
            ->andReturnSelf();

        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldReceive('createOrUpdateNewsLanguage')
            ->once();

        $this->newsToCategory
            ->shouldNotReceive(
                'where',
                'update',
            );

        $this->news
            ->shouldReceive('update')
            ->once()
            ->andReturnSelf();

        $collection = $this->mockAnything(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = new StdClass;
        $language->language_id = 1;
        $collection
            ->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $collection
            ->expects($this->once())
            ->method('first')
            ->willReturn($language);
        $this->languageHelper
            ->expects($this->once())
            ->method('getLanguages')
            ->willReturn($collection);

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->update($this->request, $newsId);

        $this->assertInstanceOf(News::class, $result);
    }

    public function testUpdateNoNewsContent()
    {
        $newsId = 1;
        $query = [
            'news_category_id' => 1,
            'news_image' => $this->faker->imageUrl,
        ];
        $this->request->query->add($query);

        $this->news
            ->shouldReceive('findOrFail')
            ->once()
            ->with($newsId)
            ->andReturnSelf();

        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket');

        $this->newsLanguage
            ->shouldNotReceive('createOrUpdateNewsLanguage');

        $this->newsToCategory
            ->shouldReceive(
                'where',
                'update',
            )
            ->once()
            ->andReturnSelf();

        $this->news
            ->shouldReceive('update')
            ->once()
            ->andReturnSelf();

        $this->languageHelper
            ->expects($this->never())
            ->method('getLanguages');

        $newsRepository = $this->getNewsRepositoryMock();
        $result = $newsRepository->update($this->request, $newsId);

        $this->assertInstanceOf(News::class, $result);
    }

    private function getNewsRepositoryMock()
    {
        return new NewsRepository(
            $this->news,
            $this->newsToCategory,
            $this->newsLanguage,
            $this->languageHelper,
            $this->helpers,
            $this->s3Helper
        );
    }

    private function mockAnything($className = null)
    {
        if ($className) {
            return $this->getMockBuilder($className);
        } else {
            return $this->getMockBuilder(StdClass::class);
        }
    }

    private function generateMocks()
    {
        $this->helpers = $this->createMock(Helpers::class);
        $this->languageHelper = $this->createMock(LanguageHelper::class);
        $this->lengthAwarePaginator = $this->createMock(LengthAwarePaginator::class);
        $this->news = Mockery::mock(News::class);  // because Mock has static methods
        $this->newsLanguage = Mockery::mock(NewsLanguage::class);
        $this->newsToCategory = Mockery::mock(NewsToCategory::class);
        $this->request = new Request;
        $this->s3Helper = $this->createMock(S3Helper::class);
    }
}
