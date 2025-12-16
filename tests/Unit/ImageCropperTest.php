<?php

namespace Sharifuddin\ImageCropper\Tests\Unit;

use Sharifuddin\ImageCropper\Tests\TestCase;
use Sharifuddin\ImageCropper\Facades\ImageCropper;

class ImageCropperTest extends TestCase
{
    /** @test */
    public function it_can_get_configuration()
    {
        $config = ImageCropper::config();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('disk', $config);
        $this->assertArrayHasKey('default_format', $config);
        $this->assertEquals('webp', $config['default_format']);
    }

    /** @test */
    public function it_can_get_default_ratios()
    {
        $ratios = ImageCropper::getDefaultRatios();
        
        $this->assertIsArray($ratios);
        $this->assertArrayHasKey('Free', $ratios);
        $this->assertArrayHasKey('Square 1:1', $ratios);
    }

    /** @test */
    public function it_can_get_default_format_and_quality()
    {
        $this->assertEquals('webp', ImageCropper::getDefaultFormat());
        $this->assertEquals(90, ImageCropper::getDefaultQuality());
    }
}