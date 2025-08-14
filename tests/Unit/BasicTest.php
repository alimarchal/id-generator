<?php

it('can instantiate IdGenerator', function () {
    $generator = new \Alimarchal\IdGenerator\IdGenerator();
    expect($generator)->toBeInstanceOf(\Alimarchal\IdGenerator\IdGenerator::class);
});

it('has working helper functions', function () {
    expect(function_exists('generateUniqueId'))->toBeTrue();
    expect(function_exists('generateUniqueIdWithPrefix'))->toBeTrue();
});