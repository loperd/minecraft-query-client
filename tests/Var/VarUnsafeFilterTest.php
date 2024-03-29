<?php

declare(strict_types=1);

namespace Loper\MinecraftQueryClient\Tests\Var;

use Loper\MinecraftQueryClient\Var\VarUnsafeFilter;
use PHPUnit\Framework\TestCase;

final class VarUnsafeFilterTest extends TestCase
{
    public function test_correct_filter_cyrillic(): void
    {
        $text = '§5 Эденор Приветствует! §r
 §3У нас лампово, залетай!';

        $result = VarUnsafeFilter::filterText($text);

        self::assertEquals('&sect;5 Эденор Приветствует! &sect;r &sect;3У нас лампово, залетай!', $result);
    }
}
