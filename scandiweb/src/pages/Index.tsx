import { useState, useEffect } from "react";
import { useQuery } from "@apollo/client";
import { GET_PRODUCTS } from "../queries";
import IndexCSS from "../../public/styles/index.module.css";
import { useNavigate, useLocation } from "react-router-dom";
import { useProduct } from "../components/ProductContext";
import Header from "../components/Header";

type Category = "all" | "clothes" | "tech";

interface Price {
  amount: number;
  currency: {
    label: string;
    symbol: string;
  };
}

interface AttributeItem {
  id: string;
  value: string;
  displayValue: string;
}

interface AttributeSet {
  id: string;
  name: string;
  type: "text" | "swatch";
  items: AttributeItem[];
}

interface productsInfo {
  id: string;
  name: string;
  prices: Price[];
  gallery: string[];
  category: Category;
  inStock: boolean;
  attributes: AttributeSet[];
}

interface OrderItem {
  product_id: string;
  name: string;
  price: number;
  currency: string;
  image: string;
  attributes: Array<{
    attributeId: string;
    attributeName: string;
    type: "text" | "swatch";
    selectedValue: string;
    selectedDisplayValue: string;
  }>;
  category: Category;
  quantity: number;
}

export default function Index() {
  const { setSelectedProductId } = useProduct();
  const navigateTo = useNavigate();
  const location = useLocation();

  const [category, setCategory] = useState<Category>("all");
  const [toggleWheneAddToCart, setToggleWheneAddToCart] =
    useState<boolean>(false);
  const [itemAdded, setItemAdded] = useState<boolean>(false);

  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    const cat = (searchParams.get("category") as Category) || "all";
    setCategory(cat);
  }, [location.search]);

  const { loading, error, data } = useQuery(GET_PRODUCTS, {
    variables: { category },
  });

  const handleQuickAddToCart = async (product: productsInfo) => {
    const firstImage = product.gallery[0];
    const priceData = product.prices[0];

    const defaultAttributes = product.attributes.map((attr) => ({
      attributeId: attr.id,
      attributeName: attr.name,
      type: attr.type,
      selectedValue: attr.items[0].value,
      selectedDisplayValue: attr.items[0].displayValue,
    }));

    const orderItem: OrderItem = {
      product_id: product.id,
      name: product.name,
      price: priceData?.amount || 0,
      currency: priceData?.currency.label || "USD",
      image: firstImage,
      attributes: defaultAttributes,
      category: product.category,
      quantity: 1,
    };

    const storedCart = localStorage.getItem("cart");
    const cart: OrderItem[] = storedCart ? JSON.parse(storedCart) : [];

    const existingIndex = cart.findIndex(
      (item: OrderItem) =>
        item.product_id === orderItem.product_id &&
        JSON.stringify(item.attributes) === JSON.stringify(orderItem.attributes)
    );

    if (existingIndex !== -1) {
      cart[existingIndex].quantity += 1;
    } else {
      cart.push(orderItem);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    setToggleWheneAddToCart((prevValue) => !prevValue);
    setItemAdded(true);
  };

  if (loading)
    return (
      <div className={`${IndexCSS.loader}`}>
        <div className={`${IndexCSS.item1}`}></div>
        <div className={`${IndexCSS.item2}`}></div>
        <div className={`${IndexCSS.item3}`}></div>
      </div>
    );
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
              const priceData = product.prices[0];
              return (
                <div
                  data-testid={`product-${kebabCaseName}`}
                  className={`${IndexCSS.productCard} relative flex flex-col gap-1 text-black`}
                  key={product.id}
                >
                  <div
                    className={`${IndexCSS.productImage} ${
                      product.inStock ? "" : "opacity-50 grayscale"
                    }`}
                    onClick={() => {
                      setSelectedProductId(product.id);
                      navigateTo(`/ProductPage?category=${category}`);
                    }}
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
                    className={`${
                      product.inStock
                        ? "cursor-pointer flex pt-5"
                        : "cursor-pointer flex pt-5"
                    }`}
                    onClick={() => {
                      setSelectedProductId(product.id);
                      navigateTo(`/ProductPage?category=${category}`);
                    }}
                  >
                    {product.name}
                  </div>
                  <div
                    className={`${IndexCSS.productPrice} ${
                      product.inStock ? "cursor-pointer flex pt-5" : "flex pt-5"
                    }`}
                    onClick={() => {
                      setSelectedProductId(product.id);
                      navigateTo(`/ProductPage?category=${category}`);
                    }}
                  >
                    {priceData?.currency?.symbol}
                    {priceData?.amount.toFixed(2)}
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
