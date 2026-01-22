<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class UserActionsSwagger
{
    // Favorites endpoints

    #[OA\Get(
        path: "/api/v1/favorites",
        tags: ["Favorites"],
        summary: "Get favorite profiles",
        description: "المفضلة",
        operationId: "getFavorites",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Favorites list - قائمة المفضلة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/ProfileCard")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function getFavorites() {}

    #[OA\Post(
        path: "/api/v1/favorites",
        tags: ["Favorites"],
        summary: "Add to favorites",
        description: "إضافة للمفضلة - Max 50 favorites - 50 مفضلة كحد أقصى",
        operationId: "addFavorite",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Added to favorites - تمت الإضافة للمفضلة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تمت الإضافة للمفضلة")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Limit reached or already favorited - تم الوصول للحد الأقصى أو مضاف مسبقاً")
        ]
    )]
    public function addFavorite() {}

    #[OA\Delete(
        path: "/api/v1/favorites/{userId}",
        tags: ["Favorites"],
        summary: "Remove from favorites",
        description: "إزالة من المفضلة",
        operationId: "removeFavorite",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "userId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Removed from favorites - تمت الإزالة من المفضلة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تمت الإزالة من المفضلة")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Not in favorites - غير موجود في المفضلة")
        ]
    )]
    public function removeFavorite() {}

    // Blocks endpoints

    #[OA\Get(
        path: "/api/v1/blocks",
        tags: ["Blocks"],
        summary: "Get blocked users",
        description: "المستخدمون المحظورون",
        operationId: "getBlocks",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Blocked users list - قائمة المستخدمين المحظورين",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "user", ref: "#/components/schemas/ProfileCard"),
                                    new OA\Property(property: "blocked_at", type: "string", format: "date-time")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function getBlocks() {}

    #[OA\Post(
        path: "/api/v1/blocks",
        tags: ["Blocks"],
        summary: "Block a user",
        description: "حظر مستخدم - Removes from matches and interests - يزيل من التوافقات والإعجابات",
        operationId: "blockUser",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer"),
                    new OA\Property(property: "reason", type: "string", maxLength: 255)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User blocked - تم حظر المستخدم",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم حظر المستخدم")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Cannot block user - لا يمكن حظر المستخدم")
        ]
    )]
    public function blockUser() {}

    #[OA\Delete(
        path: "/api/v1/blocks/{userId}",
        tags: ["Blocks"],
        summary: "Unblock a user",
        description: "إلغاء حظر مستخدم",
        operationId: "unblockUser",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "userId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User unblocked - تم إلغاء حظر المستخدم",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم إلغاء حظر المستخدم")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "User not blocked - المستخدم غير محظور")
        ]
    )]
    public function unblockUser() {}

    // Reports endpoint

    #[OA\Post(
        path: "/api/v1/reports",
        tags: ["Reports"],
        summary: "Report a user",
        description: "الإبلاغ عن مستخدم",
        operationId: "reportUser",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "reason"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer"),
                    new OA\Property(
                        property: "reason",
                        type: "string",
                        enum: ["inappropriate_photos", "offensive_content", "fake_profile", "harassment", "contact_info", "other"]
                    ),
                    new OA\Property(property: "description", type: "string", maxLength: 500)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Report submitted - تم إرسال البلاغ",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم إرسال البلاغ، شكراً لمساعدتنا في الحفاظ على المجتمع")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Cannot report user - لا يمكن الإبلاغ عن المستخدم")
        ]
    )]
    public function reportUser() {}
}
