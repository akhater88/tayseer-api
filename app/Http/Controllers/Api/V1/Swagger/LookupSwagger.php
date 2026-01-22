<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class LookupSwagger
{
    #[OA\Get(
        path: "/api/v1/lookups/countries",
        tags: ["Lookups"],
        summary: "Get all countries",
        description: "قائمة الدول",
        operationId: "getCountries",
        responses: [
            new OA\Response(
                response: 200,
                description: "Countries list - قائمة الدول",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name_ar", type: "string", example: "السعودية"),
                                    new OA\Property(property: "name_en", type: "string", example: "Saudi Arabia"),
                                    new OA\Property(property: "code", type: "string", example: "SA"),
                                    new OA\Property(property: "phone_code", type: "string", example: "+966")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getCountries() {}

    #[OA\Get(
        path: "/api/v1/lookups/cities",
        tags: ["Lookups"],
        summary: "Get cities by country",
        description: "قائمة المدن حسب الدولة",
        operationId: "getCities",
        parameters: [
            new OA\Parameter(
                name: "country_id",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cities list - قائمة المدن",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "country_id", type: "integer", example: 1),
                                    new OA\Property(property: "name_ar", type: "string", example: "الرياض"),
                                    new OA\Property(property: "name_en", type: "string", example: "Riyadh")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getCities() {}

    #[OA\Get(
        path: "/api/v1/lookups/nationalities",
        tags: ["Lookups"],
        summary: "Get all nationalities",
        description: "قائمة الجنسيات",
        operationId: "getNationalities",
        responses: [
            new OA\Response(
                response: 200,
                description: "Nationalities list - قائمة الجنسيات",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name_ar", type: "string", example: "سعودي"),
                                    new OA\Property(property: "name_en", type: "string", example: "Saudi")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getNationalities() {}

    #[OA\Get(
        path: "/api/v1/lookups/work-fields",
        tags: ["Lookups"],
        summary: "Get work fields",
        description: "قائمة مجالات العمل",
        operationId: "getWorkFields",
        responses: [
            new OA\Response(
                response: 200,
                description: "Work fields list - قائمة مجالات العمل",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name_ar", type: "string", example: "تقنية المعلومات"),
                                    new OA\Property(property: "name_en", type: "string", example: "Information Technology")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getWorkFields() {}

    #[OA\Get(
        path: "/api/v1/lookups/enums",
        tags: ["Lookups"],
        summary: "Get all enum values",
        description: "جميع القيم الثابتة (الجنس، الحالة الاجتماعية، إلخ)",
        operationId: "getEnums",
        responses: [
            new OA\Response(
                response: 200,
                description: "All enums - جميع القيم الثابتة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "gender",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "marital_status",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "religious_level",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "prayer_level",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "smoking",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "hijab_type",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "beard_type",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "skin_color",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "body_type",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "education_level",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                ),
                                new OA\Property(
                                    property: "guardian_relationship",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/EnumValue")
                                )
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function getEnums() {}
}
