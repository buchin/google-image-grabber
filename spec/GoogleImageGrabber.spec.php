<?php
use Buchin\GoogleImageGrabber\GoogleImageGrabber;

describe('GoogleImageGrabber', function ()
{
	describe('::grab($keyword, $options)', function ()
	{
		it('get images data from google images', function()
		{
			$images = GoogleImageGrabber::grab('makan nasi pake telor');

			expect(count($images))->toBeGreaterThan(0);
		});
	});

	describe('::grab($keyword, $proxy = "", $options = [], $queryParams = [])', function ()
	{
		it('Searching in a specific language (spanish)', function()
		{
                        $queryParams = ['hl' => 'es'];
			$images = GoogleImageGrabber::grab('php en español', '', [], $queryParams);
			expect(count($images))->toBeGreaterThan(0);
		});
	});
});
