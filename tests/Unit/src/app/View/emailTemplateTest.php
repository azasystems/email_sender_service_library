<?php


namespace Tests\Unit\src\app\View;

use Codeception\Test\Unit;

use function AzaSystems\App\View\getEmailTemplate;
require_once __DIR__ . '/../../../../../src/app/View/emailTemplate.php';

/*
 * Тест для шаблона письма
 * */
class emailTemplateTest extends Unit
{
    // tests
    public function testEmailTemplate()
    {
        $template = getEmailTemplate('user1', 1);
        $this->assertCount(2, $template);
    }
}
