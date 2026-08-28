<?php
it('returns health', function () {
    $this->getJson('/api/health')->assertOk()->assertJsonPath('data.ok', true);
});
