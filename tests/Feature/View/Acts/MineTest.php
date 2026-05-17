<?php

it('can render', function () {
    $contents = $this->view('acts.mine', [
        //
    ]);

    $contents->assertSee('');
});
