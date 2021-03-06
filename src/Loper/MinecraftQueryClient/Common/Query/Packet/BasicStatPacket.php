<?php

declare(strict_types=1);

namespace Loper\MinecraftQueryClient\Common\Query\Packet;

use Loper\MinecraftQueryClient\Packet;
use Loper\MinecraftQueryClient\Service\VarUnsafeFilter;
use Loper\MinecraftQueryClient\Stream\InputStream;
use Loper\MinecraftQueryClient\Stream\OutputStream;
use Loper\MinecraftQueryClient\Structure\ProtocolVersion;

final class BasicStatPacket implements Packet
{
    public const PACKET_ID = 0x00;

    public int $sessionId;
    public int $challengeToken;

    // Response data
    public string $motd;
    public string $map;
    public int $numPlayers;
    public int $maxPlayers;
    public int $port;
    public string $host;

    public function getPacketId(): int
    {
        return self::PACKET_ID;
    }

    public function read(InputStream $is, ProtocolVersion $protocol): void
    {
        // Remove Type & Session ID
        $is->readBytes(5);

        $rawMotd = $is->readString()->bytes();
        $this->motd = VarUnsafeFilter::filter($rawMotd);
        // Remove Game Type
        $is->readString();

        $this->map = VarUnsafeFilter::filter($is->readString()->bytes());
        $this->numPlayers = (int) VarUnsafeFilter::filter($is->readString()->bytes());
        $this->maxPlayers = (int) VarUnsafeFilter::filter($is->readString()->bytes());
        $this->port = $is->readLittleEndianUnsignedShort();
        $this->host = VarUnsafeFilter::filter($is->readString()->bytes());
    }

    public function write(OutputStream $os, ProtocolVersion $protocol): void
    {
        $os->writeInt($this->sessionId);
        $os->writeInt($this->challengeToken);
    }
}
