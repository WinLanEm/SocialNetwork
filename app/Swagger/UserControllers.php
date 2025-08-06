<?php

namespace App\Swagger;

class UserControllers
{
    /**
     * @OA\Get(
     *     path="/api/search/users",
     *     summary="Поиск пользователей по username",
     *     operationId="searchUsers",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="Текст для поиска пользователей по username",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Массив пользователей, соответствующих условию поиска",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 description="Результат поиска пользователя",
     *                 required={"id", "username", "avatar_url", "last_seen"},
     *                 @OA\Property(property="id", type="integer", example=123, description="ID пользователя"),
     *                 @OA\Property(property="username", type="string", example="ivan_ivanov", description="Уникальное имя пользователя"),
     *                 @OA\Property(property="avatar_url", type="string", format="url", example="https://site.ru/storage/avatars/1.png", description="URL аватара"),
     *                 @OA\Property(property="last_seen", type="string", format="date-time", example="2024-08-05T12:34:56Z", description="Время последнего посещения пользователя в формате ISO 8601")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Неавторизован - требуется авторизация через Sanctum cookie")
     * )
     */
    public function searchUsers(){}

    /**
     * @OA\Patch(
     *     path="/api/update/profile",
     *     summary="Обновить профиль пользователя",
     *     operationId="updateProfile",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", maxLength=511, example="cool_hacker", description="Новый username, макс 511 символов, уникален среди всех пользователей"),
     *             @OA\Property(property="phone", type="string", pattern="^(\+7|8)[0-9]{10}$", example="+79998887766", description="Номер телефона в формате +7XXXXXXXXXX или 8XXXXXXXXXX, уникальный"),
     *             @OA\Property(property="bio", type="string", example="Люблю Laravel и шашлыки", description="О себе"),
     *             @OA\Property(property="password", type="string", minLength=8, example="SecretABC1", description="Новый пароль, минимум 8 символов, минимум одна заглавная буква"),
     *             @OA\Property(property="password_confirmation", type="string", example="SecretABC1", description="Подтверждение пароля, должно совпадать с password"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Профиль успешно обновлен",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"username", "bio", "avatar_url", "phone"},
     *             @OA\Property(property="username", type="string", example="cool_hacker"),
     *             @OA\Property(property="bio", type="string", example="Люблю Laravel и шашлыки"),
     *             @OA\Property(property="avatar_url", type="string", format="url", example="https://site.ru/storage/avatars/avatar.png"),
     *             @OA\Property(property="phone", type="string", example="+79998887766")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Валидационная ошибка (ошибки в данных запроса)"),
     *     @OA\Response(response=401, description="Неавторизован - требуется авторизация через Sanctum cookie")
     * )
     */
    public function updateUserProfile(){}

    /**
     * @OA\Post(
     *     path="/api/update/avatar",
     *     summary="Обновить аватар пользователя",
     *     operationId="updateAvatar",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"avatar_url"},
     *                 @OA\Property(
     *                     property="avatar_url",
     *                     type="string",
     *                     format="binary",
     *                     description="Изображение аватара (jpeg, png, jpg, gif, webp, svg), макс 2МБ"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Аватар успешно обновлен",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"username", "bio", "avatar_url", "phone"},
     *             @OA\Property(property="username", type="string", example="cool_hacker"),
     *             @OA\Property(property="bio", type="string", example="Люблю Laravel и шашлыки"),
     *             @OA\Property(property="avatar_url", type="string", format="url", example="https://site.ru/storage/avatars/avatar.png"),
     *             @OA\Property(property="phone", type="string", example="+79998887766")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Валидация не прошла (файл не изображение/превышен размер)"),
     *     @OA\Response(response=401, description="Неавторизован - требуется авторизация через Sanctum cookie")
     * )
     */
    public function updateAvatar(){}

    /**
     * @OA\Post(
     *     path="/api/update/last_seen",
     *     summary="Обновить время последнего посещения пользователя",
     *     operationId="updateLastSeen",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"last_seen"},
     *             @OA\Property(
     *                 property="last_seen",
     *                 type="string",
     *                 format="date-time",
     *                 example="2025-08-06T09:00:00Z",
     *                 description="Время последней активности пользователя в ISO8601 (UTC)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Время успешно обновлено, тело ответа пустое"
     *     ),
     *     @OA\Response(response=401, description="Неавторизован - требуется авторизация через Sanctum cookie"),
     * )
     */
    public function updateLstSeen(){}

}
