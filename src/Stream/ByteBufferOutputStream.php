<?php

declare(strict_types=1);

namespace Loper\MinecraftQueryClient\Stream;

use Loper\MinecraftQueryClient\Var\VarTypeFactory;
use PHPinnacle\Buffer\ByteBuffer;

final class ByteBufferOutputStream implements BufferedOutputStream
{
    public function __construct(private readonly ByteBuffer $buffer)
    {
    }

    /**
     * @param int[]|ByteBuffer|string $bytes
     */
    public function writeBytes(array|ByteBuffer|string $bytes): void
    {
        if (is_array($bytes)) {
            $bytes = ArrayByteBufferConverter::convert($bytes);
        }

        $this->buffer->append($bytes);
    }

    public function writeByte(int $byte): void
    {
        $this->buffer->appendInt8($byte);
    }

    public function writeShort(int $short): void
    {
        $this->buffer->appendInt16($short);
    }

    public function writeInt(int $integer): void
    {
        $this->buffer->appendInt32($integer);
    }

    public function writeLong(int $long): void
    {
        $this->buffer->appendInt64($long);
    }

    public function writeVarInt(int $data): void
    {
        $this->buffer->append(VarTypeFactory::createVarInt($data));
    }

    public function getBuffer(): ByteBuffer
    {
        return $this->buffer;
    }

    public function writeVarString(string $value): void
    {
        $this->writeByte(\strlen($value));
        $this->writeBytes($value);
    }
}
