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
	// params URLs
	describe('::getSearchURLParams($params)', function ()
	{
		it('check sizes viables', function()
		{
			$params = GoogleImageGrabber::getSearchURLParams([
				'size' => 'small'
			]);
			expect($params)->toContainKey(['q', 'source', 'tbm', 'tbs']);
			expect($params['tbs'])->toBe('isz:i');
		});
		it('Filter pornography, potentially offensive and inappropriate content', function()
		{
			$input = [
				'keyword' => 'pretty woman',
				'safe' => 'active'
			];
			$params = GoogleImageGrabber::getSearchURLParams($input);
			expect($params)->toContainKey(['q', 'source', 'tbm', 'tbs', 'safe']);
			expect($params['safe'])->toBe('active');
		});
		it('general testing', function()
		{
			$input = [
				'keyword' => 'Logotipo PHP',
				'locate' => 'es',
				'license' => 'creative_commons',
				'size' => 'small'
			];
			$params = GoogleImageGrabber::getSearchURLParams($input);
			expect(isset($params['keyword']))->toBe(false);
			expect(isset($params['q']))->toBe(true);
			expect($params['q'])->toBe($input['keyword']);
			// language
			expect($params['hl'])->toBe('es');
			// License and Size
			expect($params['tbs'])->toBe('il:cl,isz:i');
			// testing grab
			$images = GoogleImageGrabber::grab($input);
			expect(count($images))->toBeGreaterThan(0);
		});
	});
});
