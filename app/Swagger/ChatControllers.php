<?php

namespace App\Swagger;

use App\Common\Controllers\Controller;

class ChatControllers extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/chats/messages",
     *     summary="Получить сообщения из чата с пагинацией",
     *     operationId="getChatMessages",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="chat_id",
     *         in="query",
     *         required=true,
     *         description="ID чата для получения сообщений",
     *         @OA\Schema(type="string"),
     *         example="chat-123abc"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         description="Номер страницы для пагинации сообщений",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список сообщений из чата с пагинацией",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="messages",
     *                 type="object",
     *                 nullable=true,
     *                 description="Пагинированный объект сообщений или null, если сообщений нет",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         required={"id","content","sender_id","updated_at","is_read"},
     *                         @OA\Property(property="id", type="integer", example=456, description="ID сообщения"),
     *                         @OA\Property(property="content", type="string", example="Привет, как дела?", description="Текст сообщения (расшифрованный)"),
     *                         @OA\Property(property="sender_id", type="integer", example=42, description="ID отправителя сообщения"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-06T12:00:00Z", description="Время последнего обновления сообщения"),
     *                         @OA\Property(property="is_read", type="boolean", example=true, description="Прочитано ли сообщение")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", format="url", example="https://api.site.ru/api/chats/messages?chat_id=chat-123abc&page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="last_page_url", type="string", format="url", example="https://api.site.ru/api/chats/messages?chat_id=chat-123abc&page=5"),
     *                 @OA\Property(property="next_page_url", type="string", format="url", nullable=true, example="https://api.site.ru/api/chats/messages?chat_id=chat-123abc&page=2"),
     *                 @OA\Property(property="path", type="string", format="url", example="https://api.site.ru/api/chats/messages"),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="prev_page_url", type="string", format="url", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещён - пользователь не участник чата",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to see this chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации параметров запроса",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "chat_id": {"Поле chat_id обязательно."},
     *                     "page": {"Поле page обязательно и должно быть целым числом."}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован - требуется Sanctum cookie-based авторизация"
     *     )
     * )
     */
    public function getChatMessages(){}

    /**
     * @OA\Post(
     *     path="/api/chats",
     *     summary="Получить существующий или создать новый чат",
     *     operationId="getOrCreateChat",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"participants", "type"},
     *             @OA\Property(
     *                 property="participants",
     *                 type="array",
     *                 description="Массив ID пользователей, которые будут участниками чата. Должен содержать как минимум одного пользователя.",
     *                 minItems=1,
     *                 @OA\Items(type="integer", example=42)
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Тип чата (например, 'private', 'group')",
     *                 example="private"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о существующем либо новом чате",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"chat_id", "last_seen", "last_message", "messages"},
     *             @OA\Property(
     *                 property="chat_id",
     *                 type="string",
     *                 example="123",
     *                 description="Уникальный идентификатор чата"
     *             ),
     *             @OA\Property(
     *                 property="last_seen",
     *                 type="string",
     *                 format="date-time",
     *                 nullable=true,
     *                 example="2025-08-06T12:00:00Z",
     *                 description="Время последнего посещения пользователя (если применимо)"
     *             ),
     *             @OA\Property(
     *                 property="last_message",
     *                 type="string",
     *                 nullable=true,
     *                 example="Привет всем!",
     *                 description="Текст последнего сообщения в чате"
     *             ),
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 description="Массив сообщений, получаемых при существующем чате (если чат новый, пустой массив)",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "content", "sender_id", "updated_at", "is_read"},
     *                     @OA\Property(property="id", type="integer", example=456, description="ID сообщения"),
     *                     @OA\Property(property="content", type="string", example="Привет, как дела?", description="Текст сообщения"),
     *                     @OA\Property(property="sender_id", type="integer", example=42, description="ID отправителя сообщения"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-06T12:00:00Z", description="Время отправки или обновления сообщения"),
     *                     @OA\Property(property="is_read", type="boolean", example=true, description="Флаг, прочитано ли сообщение")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещён. Пользователь не является участником указанного чата",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to see this chat")
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
     *                     "participants": {"You can not send empty participants"},
     *                     "type": {"Type is required field"}
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
    public function getOrCreateChat(){}

    /**
     * @OA\Delete(
     *     path="/api/chats/{chat}",
     *     summary="Удалить чат",
     *     operationId="destroyChat",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         description="ID чата для удаления",
     *         required=true,
     *         @OA\Schema(type="string", example="123")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Чат успешно удалён, тело ответа отсутствует"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещён. Пользователь не является участником данного чата или у него нет прав на удаление",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to delete this chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован - требуется авторизация через Sanctum cookie"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Чат с указанным ID не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Chat not found")
     *         )
     *     )
     * )
     */
    public function destroyChat(string $chat){}

}
