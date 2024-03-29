<?php

declare(strict_types=1);

namespace Loper\MinecraftQueryClient\Tests\Common\Query;

use DG\BypassFinals;
use Loper\MinecraftQueryClient\Address\ServerAddress;
use Loper\MinecraftQueryClient\Address\ServerAddressType;
use Loper\MinecraftQueryClient\Common\Query\Packet\HandshakePacket;
use Loper\MinecraftQueryClient\Common\Query\QueryMinecraftClient;
use Loper\MinecraftQueryClient\Exception\PacketReadException;
use Loper\MinecraftQueryClient\Exception\PacketSendException;
use Loper\Minecraft\Protocol\Struct\JavaProtocolVersion;
use Loper\MinecraftQueryClient\Tests\Helper\ReflectionHelper;
use Loper\MinecraftQueryClient\Tests\TestPacket;
use PHPinnacle\Buffer\ByteBuffer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Socket\Raw\Socket;

class QueryMinecraftClientTest extends TestCase
{
    /**
     * @return \Loper\MinecraftQueryClient\Common\Query\QueryMinecraftClient
     */
    public function getQueryClient(?Socket $socket = null): QueryMinecraftClient
    {
        $socket ??= $this->createSocket();

        return $this->createQueryClient($socket);
    }

    public function createQueryClient(
        Socket $socket,
        ServerAddress $serverAddress = new ServerAddress(
            ServerAddressType::Dedicated,
            '1.1.1.1',
            '1.1.1.1'
        )
    ): QueryMinecraftClient {
        $reflection = new \ReflectionClass(QueryMinecraftClient::class);
        $queryClient = $reflection->newInstanceWithoutConstructor();
        ReflectionHelper::setProperties(
            $reflection,
            $queryClient,
            [
                'socket' => $socket,
                'serverAddress' => $serverAddress
            ]
        );

        return $queryClient;
    }

    /**
     * @param int $sendResult - size of written bytes
     */
    private function createSocket(string $socketData = 'CQAABwIxMjMyODkzNwA=', int $sendResult = 7): Socket&MockObject
    {
        $mockSocket = $this->createMock(Socket::class);
        $mockSocket->method('read')->withAnyParameters()->willReturn(base64_decode($socketData, true));
        $mockSocket->method('send')->withAnyParameters()->willReturn($sendResult);

        return $mockSocket;
    }

    protected function setUp(): void
    {
        BypassFinals::enable();
    }

    public function test_query_send_packet(): void
    {
        $socket = $this->createSocket(socketData: 'AA==');
        $socket->expects($this->atLeastOnce())->method('read')->withAnyParameters();

        $queryClient = $this->getQueryClient($socket);
        $packet = new TestPacket();

        $queryClient->sendPacket($packet, JavaProtocolVersion::JAVA_1_20_1);

        $this->assertTrue($packet->readed);
    }

    public function test_query_send_packet_exception(): void
    {
        $this->expectException(PacketSendException::class);

        $socket = $this->createSocket(sendResult: 1);

        $queryClient = $this->getQueryClient($socket);
        $packet = new HandshakePacket();
        $packet->sessionId = 1794;

        $queryClient->sendPacket($packet, JavaProtocolVersion::JAVA_1_20_1);
    }

    public function test_query_read_packet_exception(): void
    {
        $this->expectException(PacketReadException::class);

        $buffer = new ByteBuffer();
        $buffer->appendInt8(1500);
        $encodeBuffer = base64_encode((string)$buffer);

        $socket = $this->createSocket(socketData: $encodeBuffer);

        $queryClient = $this->getQueryClient($socket);
        $packet = new HandshakePacket();
        $packet->sessionId = 1794;

        $queryClient->sendPacket($packet, JavaProtocolVersion::JAVA_1_20_1);
    }
}
