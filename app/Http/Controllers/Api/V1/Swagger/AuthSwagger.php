<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class AuthSwagger
{
    #[OA\Post(
        path: "/api/v1/auth/send-otp",
        tags: ["Authentication"],
        summary: "Send OTP to phone number",
        description: "إرسال رمز التحقق للهاتف - Sends a 6-digit OTP via SMS for verification",
        operationId: "sendOtp",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["phone"],
                properties: [
                    new OA\Property(property: "phone", type: "string", example: "+966501234567", description: "Phone with country code - رقم الهاتف مع رمز الدولة"),
                    new OA\Property(property: "purpose", type: "string", enum: ["registration", "login", "password_reset"], example: "registration", description: "OTP purpose - الغرض من OTP")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "OTP sent successfully - تم إرسال رمز التحقق بنجاح",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم إرسال رمز التحقق"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "expires_in", type: "integer", example: 300, description: "OTP validity in seconds"),
                                new OA\Property(property: "resend_available_in", type: "integer", example: 60, description: "Cooldown before resend")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: "Too many requests - طلبات كثيرة جداً",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "يرجى الانتظار قبل إعادة المحاولة"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "retry_after", type: "integer", example: 45)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function sendOtp() {}

    #[OA\Post(
        path: "/api/v1/auth/verify-otp",
        tags: ["Authentication"],
        summary: "Verify OTP code",
        description: "التحقق من رمز OTP - Verifies the OTP and returns temp token for registration or auth token for login",
        operationId: "verifyOtp",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["phone", "code", "purpose"],
                properties: [
                    new OA\Property(property: "phone", type: "string", example: "+966501234567"),
                    new OA\Property(property: "code", type: "string", example: "123456", minLength: 6, maxLength: 6),
                    new OA\Property(property: "purpose", type: "string", enum: ["registration", "login", "password_reset"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "OTP verified - تم التحقق من الرمز",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم التحقق بنجاح"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "temp_token", type: "string", description: "For registration purpose - للتسجيل"),
                                new OA\Property(property: "token", type: "string", description: "For login purpose - لتسجيل الدخول"),
                                new OA\Property(property: "user", ref: "#/components/schemas/User", description: "For login purpose")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Invalid or expired OTP - رمز غير صالح أو منتهي الصلاحية"),
            new OA\Response(response: 429, description: "Maximum attempts exceeded - تم تجاوز الحد الأقصى للمحاولات")
        ]
    )]
    public function verifyOtp() {}

    #[OA\Get(
        path: "/api/v1/auth/check-username",
        tags: ["Authentication"],
        summary: "Check username availability",
        description: "التحقق من توفر اسم المستخدم",
        operationId: "checkUsername",
        parameters: [
            new OA\Parameter(
                name: "username",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string", minLength: 4, maxLength: 15, example: "ahmed_123")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Availability check result - نتيجة التحقق من التوفر",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "available", type: "boolean", example: true),
                                new OA\Property(property: "message", type: "string", example: "اسم المستخدم متاح")
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function checkUsername() {}

    #[OA\Post(
        path: "/api/v1/auth/register",
        tags: ["Authentication"],
        summary: "Register new user",
        description: "تسجيل مستخدم جديد - Complete registration after phone verification. Account is active immediately.",
        operationId: "register",
        parameters: [
            new OA\Parameter(
                name: "X-Temp-Token",
                in: "header",
                required: true,
                description: "Temporary token from verify-otp - رمز مؤقت من التحقق",
                schema: new OA\Schema(type: "string")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "password", "password_confirmation", "gender", "date_of_birth", "country_id", "city_id", "marital_status", "religious_level", "prayer_level", "about_me", "full_name", "declaration_accepted", "terms_accepted"],
                properties: [
                    new OA\Property(property: "username", type: "string", minLength: 4, maxLength: 15, example: "ahmed_m", description: "Letters, numbers, dots, underscores only - أحرف وأرقام ونقاط وشرطات سفلية فقط"),
                    new OA\Property(property: "password", type: "string", minLength: 8, example: "SecurePass123", description: "Min 8 chars, 1 uppercase, 1 number - 8 أحرف على الأقل، حرف كبير، رقم"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "SecurePass123"),
                    new OA\Property(property: "gender", type: "string", enum: ["male", "female"], example: "male"),
                    new OA\Property(property: "date_of_birth", type: "string", format: "date", example: "1992-05-15", description: "Must be 18+ - يجب أن يكون 18+"),
                    new OA\Property(property: "nationality_id", type: "integer", example: 1),
                    new OA\Property(property: "country_id", type: "integer", example: 1),
                    new OA\Property(property: "city_id", type: "integer", example: 1),
                    new OA\Property(property: "marital_status", type: "string", enum: ["single", "divorced", "widowed", "married"], example: "single", description: "'married' only for males - 'متزوج' للذكور فقط"),
                    new OA\Property(property: "number_of_children", type: "integer", minimum: 0, maximum: 20, example: 0),
                    new OA\Property(property: "number_of_wives", type: "integer", minimum: 0, maximum: 3, example: 0, description: "Males only, if married - للذكور فقط إذا كان متزوجاً"),
                    new OA\Property(property: "height_cm", type: "integer", minimum: 140, maximum: 210, example: 175),
                    new OA\Property(property: "weight_kg", type: "integer", minimum: 40, maximum: 150, example: 75),
                    new OA\Property(property: "skin_color", type: "string", enum: ["very_light", "light", "wheatish", "brown", "dark"], example: "wheatish"),
                    new OA\Property(property: "body_type", type: "string", enum: ["slim", "athletic", "average", "curvy", "heavy"], example: "athletic"),
                    new OA\Property(property: "religious_level", type: "string", enum: ["very_religious", "religious", "moderate", "not_religious"], example: "religious"),
                    new OA\Property(property: "prayer_level", type: "string", enum: ["all_prayers", "most_prayers", "some_prayers", "rarely", "never"], example: "all_prayers"),
                    new OA\Property(property: "smoking", type: "string", enum: ["no", "yes", "occasionally", "quit"], example: "no"),
                    new OA\Property(property: "beard_type", type: "string", enum: ["full_beard", "light_beard", "no_beard"], example: "full_beard", description: "Males only - للذكور فقط"),
                    new OA\Property(property: "hijab_type", type: "string", enum: ["niqab", "hijab", "no_hijab"], example: "hijab", description: "Females only - للإناث فقط"),
                    new OA\Property(property: "education_level", type: "string", enum: ["high_school", "diploma", "bachelors", "masters", "phd", "other"], example: "bachelors"),
                    new OA\Property(property: "work_field_id", type: "integer", example: 3),
                    new OA\Property(property: "job_title", type: "string", maxLength: 100, example: "Software Engineer"),
                    new OA\Property(property: "about_me", type: "string", minLength: 50, maxLength: 500, example: "أنا شاب طموح أبحث عن شريكة حياة...", description: "No contact info allowed - لا يسمح بمعلومات الاتصال"),
                    new OA\Property(property: "partner_preferences", type: "string", maxLength: 500, example: "أبحث عن فتاة ملتزمة..."),
                    new OA\Property(property: "full_name", type: "string", maxLength: 100, example: "أحمد محمد", description: "Private, not shown to others - خاص، لا يظهر للآخرين"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "ahmed@email.com", description: "Private, optional - خاص، اختياري"),
                    new OA\Property(property: "is_convert", type: "boolean", example: false, description: "Females only: true if new Muslim without guardian - للإناث فقط: صحيح إذا كانت مسلمة جديدة بدون ولي"),
                    new OA\Property(property: "guardian_name", type: "string", maxLength: 100, example: "محمد أحمد", description: "Required for non-convert females - مطلوب للإناث غير المحتديات"),
                    new OA\Property(property: "guardian_phone", type: "string", example: "+966509876543", description: "Required for non-convert females"),
                    new OA\Property(property: "guardian_relationship", type: "string", enum: ["father", "brother", "son", "uncle", "grandfather"], example: "father", description: "Required for non-convert females"),
                    new OA\Property(property: "declaration_accepted", type: "boolean", example: true, description: "Marriage intention declaration - إقرار نية الزواج"),
                    new OA\Property(property: "terms_accepted", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Registration successful - تم التسجيل بنجاح",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم التسجيل بنجاح"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string", example: "1|abc123..."),
                                new OA\Property(property: "guardian_status", type: "string", enum: ["not_required", "pending_invitation"], description: "For females - للإناث")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Invalid temp token - رمز مؤقت غير صالح"),
            new OA\Response(response: 422, description: "Validation errors - أخطاء التحقق")
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/api/v1/auth/login",
        tags: ["Authentication"],
        summary: "Login with phone and password",
        description: "تسجيل الدخول بالهاتف وكلمة المرور",
        operationId: "login",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["phone", "password"],
                properties: [
                    new OA\Property(property: "phone", type: "string", example: "+966501234567"),
                    new OA\Property(property: "password", type: "string", example: "SecurePass123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful - تم تسجيل الدخول بنجاح",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Invalid credentials - بيانات اعتماد غير صالحة")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/api/v1/auth/logout",
        tags: ["Authentication"],
        summary: "Logout current user",
        description: "تسجيل الخروج",
        operationId: "logout",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logged out successfully - تم تسجيل الخروج بنجاح",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم تسجيل الخروج")
                    ]
                )
            )
        ]
    )]
    public function logout() {}
}
