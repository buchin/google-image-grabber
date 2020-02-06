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
});