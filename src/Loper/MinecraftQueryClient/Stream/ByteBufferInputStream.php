<?php

declare(strict_types=1);

namespace Loper\MinecraftQueryClient\Stream;

use Loper\MinecraftQueryClient\Var\VarTypeReader;
use PHPinnacle\Buffer\ByteBuffer;

final class ByteBufferInputStream implements InputStream
{
    public function __construct(private readonly ByteBuffer $buffer)
    {
    }

    public function readFullData(int $maxReadBytes = self::MAX_READ_BYTES): ByteBuffer
    {
        if ($this->buffer->size() > $maxReadBytes) {
            throw new \RuntimeException('Too big data.');
        }

        return new ByteBuffer($this->buffer->consume($this->buffer->size()));
    }

    public function readVarInt(int $maxBytes = 5): int
    {
        return VarTypeReader::readVarInt($this, $maxBytes);
    }

    public function readByte(): int
    {
        return $this->buffer->consumeInt8();
    }

    public function readBytes(int $size): ByteBuffer
    {
        return new ByteBuffer($this->buffer->consume($size));
    }

    public function readShort(): int
    {
        return $this->readBytes(2)->consumeInt16();
    }

    public function readInt(): int
    {
        return $this->readBytes(4)->consumeInt32();
    }

    public function readLong(): int
    {
        return $this->readBytes(8)->consumeInt64();
    }

    public function readString(): ByteBuffer
    {
        $pos = \strpos($this->buffer->bytes(), "\x0");

        if (false === $pos) {
            return new ByteBuffer();
        }

        return $this->readBytes($pos);
    }
}
