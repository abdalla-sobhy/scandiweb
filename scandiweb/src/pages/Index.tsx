import { useState } from "react";
import { useQuery } from "@apollo/client";
import { GET_PRODUCTS } from "../queries";
import IndexCSS from "../../public/styles/index.module.css";
import { useNavigate } from "react-router-dom";
import { useProduct } from "../components/ProductContext";
import Header from "../components/Header";

type Category = "all" | "clothes" | "tech";

interface productsInfo {
id: string;
name: string;
price: number;
gallery: string[];
category: Category;
inStock: boolean;
}

interface OrderItem {
product_id: string;
name: string;
price: number;
image: string;
size: "XS" | "S" | "M" | "L";
color: "gray" | "black" | "green";
category: Category;
quantity: number;
}

export default function Index() {
const { setSelectedProductId } = useProduct();
const [toggleWheneAddToCart, setToggleWheneAddToCart] =
    useState<boolean>(false);
const [category, setCategory] = useState<Category>("all");
const [itemAdded, setItemAdded] = useState<boolean>(false);
const navigateTo = useNavigate();

const { loading, error, data } = useQuery(GET_PRODUCTS, {
    variables: { category },
});

const handleQuickAddToCart = async (product: productsInfo) => {
    const firstImage = product.gallery[0];
    const orderItem: OrderItem = {
    product_id: product.id,
    name: product.name,
    price: product.price,
    image: firstImage,
    size: "XS",
    color: "gray",
    category: product.category,
    quantity: 1,
    };

    const storedCart = localStorage.getItem("cart");
    let cart: OrderItem[] = [];
    if (storedCart) {
    try {
        cart = JSON.parse(storedCart) as OrderItem[];
    } catch (error) {
        console.error("Error parsing cart from localStorage:", error);
        cart = [];
    }
    }

    const existingIndex = cart.findIndex(
    (item: OrderItem) =>
        item.product_id === orderItem.product_id &&
        item.size === orderItem.size &&
        item.color === orderItem.color
    );

    if (existingIndex !== -1) {
    cart[existingIndex].quantity += orderItem.quantity;
    } else {
    cart.push(orderItem);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    setToggleWheneAddToCart((prevValue) => !prevValue);
    setItemAdded(true);
};

if (loading) return (
<div className={`${IndexCSS.loader}`}>
    <div className={`${IndexCSS.item1}`}></div>
    <div className={`${IndexCSS.item2}`}></div>
    <div className={`${IndexCSS.item3}`}></div>
</div>
)
if (error) return <div>Error: {error.message}</div>;

return (
    <div className="h-full w-full flex-col flex gap-12 bg-white">
    <Header
        category={category}
        setCategory={setCategory}
        toggleWheneAddToCart={toggleWheneAddToCart}
        itemAdded={itemAdded}
    />
    <div className={IndexCSS.categoryTitle}>
        {category ? category : "Unknown"}
    </div>
    <div className="w-full justify-center flex mt-20 pb-12 bg-white">
        {data?.products.length === 0 ? (
        <div className={IndexCSS.noProducts}>
            No Products found for {category}. Check out later.
        </div>
        ) : (
        <div className={IndexCSS.cardsContainer}>
            {data?.products.map((product: productsInfo) => {
            const kebabCaseName = product.name
                .toLowerCase()
                .replace(/\s+/g, "-");
            return (
                <div
                data-testid={`product-${kebabCaseName}`}
                className={`${IndexCSS.productCard} relative flex flex-col gap-1 text-black`}
                key={product.id}
                >
                <div
                    className={`${IndexCSS.productImage} ${
                    product.inStock
                        ? "cursor-pointer"
                        : "opacity-50 grayscale"
                    }`}
                    onClick={() =>
                    product.inStock
                        ? (setSelectedProductId(product.id),
                        navigateTo(`/ProductPage?category=${category}`))
                        : null
                    }
                >
                    {!product.inStock && (
                    <div className={IndexCSS.outOfStockImage}>
                        <img
                        src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/out%20of%20stock.svg"
                        alt=""
                        />
                    </div>
                    )}
                    <img
                    className="h-full w-full"
                    src={product.gallery[0]}
                    alt=""
                    />
                </div>
                {product.inStock && (
                    <img
                    className={`${IndexCSS.circleIcon} cursor-pointer`}
                    onClick={() => {
                        handleQuickAddToCart(product);
                    }}
                    src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/Circle%20Icon.svg"
                    alt=""
                    />
                )}
                <div
                    className={`${IndexCSS.productName} ${
                    product.inStock ? "cursor-pointer flex pt-5" : "flex pt-5"
                    }`}
                    onClick={() =>
                    product.inStock
                        ? (setSelectedProductId(product.id),
                        navigateTo(`/ProductPage?category=${category}`))
                        : null
                    }
                >
                    {product.name}
                </div>
                <div
                    className={`${IndexCSS.productPrice} ${
                    product.inStock ? "cursor-pointer flex pt-5" : "flex pt-5"
                    }`}
                    onClick={() =>
                    product.inStock
                        ? (setSelectedProductId(product.id),
                        navigateTo(`/ProductPage?category=${category}`))
                        : null
                    }
                >
                    ${product.price.toFixed(2)}
                </div>
                </div>
            );
            })}
        </div>
        )}
    </div>
    </div>
);
}
