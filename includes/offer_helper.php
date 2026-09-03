<?php

function getActiveOffer(PDO $pdo, int $productID): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            pr.PromotionId,
            pr.OfferType,
            pr.DiscountValue,
            pr.BuyQuantity,
            pr.GetQuantity,
            pr.StartDate,
            pr.EndDate,
            pr.SellerId
        FROM promotion pr
        INNER JOIN applies_to a
            ON pr.PromotionId = a.promotionID
        WHERE a.productID = ?
          AND NOW() BETWEEN pr.StartDate AND pr.EndDate
        ORDER BY pr.PromotionId DESC
        LIMIT 1
    ");

    $stmt->execute([$productID]);

    $offer = $stmt->fetch();

    return $offer ?: null;
}


function getOfferPrice(float $price, ?array $offer): float
{
    if (!$offer) {
        return $price;
    }

    if ($offer["OfferType"] === "Percentage") {

        $discount = (float) $offer["DiscountValue"];

        return max(
            0,
            $price - ($price * $discount / 100)
        );
    }

    return $price;
}


function calculateOfferQuantity(
    int $paidQuantity,
    ?array $offer
): array {

    if (!$offer) {

        return [
            "paidQuantity" => $paidQuantity,
            "freeQuantity" => 0,
            "totalQuantity" => $paidQuantity
        ];
    }

    if ($offer["OfferType"] !== "BuyXGetY") {

        return [
            "paidQuantity" => $paidQuantity,
            "freeQuantity" => 0,
            "totalQuantity" => $paidQuantity
        ];
    }

    $buy = max(
        1,
        (int) $offer["BuyQuantity"]
    );

    $get = max(
        0,
        (int) $offer["GetQuantity"]
    );

    $freeQuantity =
        intdiv($paidQuantity, $buy) * $get;

    return [
        "paidQuantity" => $paidQuantity,
        "freeQuantity" => $freeQuantity,
        "totalQuantity" => $paidQuantity + $freeQuantity
    ];
}