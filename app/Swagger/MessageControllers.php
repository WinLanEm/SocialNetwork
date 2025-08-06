<?php

namespace App\Swagger;

use App\Common\Controllers\Controller;

class MessageControllers extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Отправить сообщение в чат",
     *     operationId="sendMessage",
     *     tags={"Messages"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "chat_id", "temp_id"},
     *             @OA\Property(
     *                 property="content",
     *                 type="string",
     *                 maxLength=65535,
     *                 example="Привет, как дела?",
     *                 description="Текст сообщения",
     *                 minLength=1
     *             ),
     *             @OA\Property(
     *                 property="chat_id",
     *                 type="string",
     *                 example="chat-123abc",
     *                 description="ID чата, куда отправляется сообщение",
     *                 minLength=1
     *             ),
     *             @OA\Property(
     *                 property="temp_id",
     *                 type="string",
     *                 example="temp-456def",
     *                 description="Временный ID сообщения на клиенте для отслеживания статуса",
     *                 minLength=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Сообщение успешно создано, без содержимого в ответе"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещён. Пользователь не является участником чата",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to see this message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации запроса",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Ошибки валидации по полям",
     *                 example={
     *                    "content": {"Сообщение не может быть пустым"},
     *                    "chat_id": {"ID чата обязателен"},
     *                    "temp_id": {"Временный ID обязателен"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован - требуется авторизация через Sanctum cookie"
     *     )
     * )
     */
    public function sendMessage(){}

    /**
     * @OA\Post(
     *     path="/api/messages/mark_messages_is_read",
     *     summary="Отметить сообщения как прочитанные",
     *     operationId="markMessagesAsRead",
     *     tags={"Messages"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_ids", "user_id", "chat_id"},
     *             @OA\Property(
     *                 property="message_ids",
     *                 description="Массив ID сообщений для отметки как прочитанных",
     *                 minItems=1,
     *                 type="array",
     *
     *                 @OA\Items(type="integer", example=123)
     *             ),
     *             @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 example=42,
     *                 description="ID пользователя, отметившего сообщения",
     *                 minimum=1
     *             ),
     *             @OA\Property(
     *                 property="chat_id",
     *                 type="string",
     *                 example="chat-123abc",
     *                 description="ID чата, содержащего сообщения",
     *                 minLength=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Успешно - тело ответа пустое"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации запроса",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Ошибки валидации по полям",
     *                 example={
     *                    "message_ids": {"Массив ID сообщений обязателен и не должен быть пустым"},
     *                    "user_id": {"ID пользователя обязателен"},
     *                    "chat_id": {"ID чата обязателен"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован - требуется авторизация через Sanctum cookie"
     *     )
     * )
     */
    public function markMessageIsRead(){}
}
