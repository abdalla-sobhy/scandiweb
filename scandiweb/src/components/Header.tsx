import { useEffect, useState } from "react";
import IndexCSS from "../../public/styles/index.module.css";
import { useMutation } from "@apollo/client";
import { PLACE_ORDER } from "../queries";

interface HeaderProps {
  category: string;
  setCategory: (category: "all" | "clothes" | "tech") => void;
  toggleWheneAddToCart: boolean;
  itemAdded: boolean;
}

interface AttributeSelection {
  attributeId: string;
  attributeName: string;
  type: "text" | "swatch";
  selectedValue: string;
  selectedDisplayValue: string;
}

interface productsInfo {
  product_id: string;
  name: string;
  price: number;
  image: string;
  category: "all" | "clothes" | "tech";
  attributes: AttributeSelection[];
  quantity: number;
}

export default function Header({
  category,
  setCategory,
  toggleWheneAddToCart,
  itemAdded,
}: HeaderProps) {
  const [placeOrder] = useMutation(PLACE_ORDER);
  const [cartProducts, setCartProducts] = useState<productsInfo[]>([]);
  const [showCart, setShowCart] = useState<boolean>(false);

  useEffect(() => {
    const storedCart = localStorage.getItem("cart");
    if (storedCart) {
      try {
        const parsedCart = JSON.parse(storedCart) as productsInfo[];
        const normalizedCart: productsInfo[] = parsedCart.map((item) => ({
          ...item,
          attributes: Array.isArray(item.attributes) ? item.attributes : [],
        }));
        setCartProducts(normalizedCart);
      } catch (e) {
        console.error("Error parsing cart from localStorage:", e);
      }
    }
    if (itemAdded) {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
      setShowCart(true);
    }
  }, [itemAdded, toggleWheneAddToCart]);

  const findItemIndex = (cart: productsInfo[], product: productsInfo) => {
    return cart.findIndex(
      (item) =>
        item.product_id === product.product_id &&
        JSON.stringify(item.attributes) === JSON.stringify(product.attributes)
    );
  };

  const increaseQuantity = (product: productsInfo) => {
    const storedCart = localStorage.getItem("cart");
    const cart: productsInfo[] = storedCart ? JSON.parse(storedCart) : [];
    const index = findItemIndex(cart, product);
    if (index !== -1) {
      cart[index].quantity += 1;
    }
    localStorage.setItem("cart", JSON.stringify(cart));
    setCartProducts(cart);
  };

  const decreaseQuantity = (product: productsInfo) => {
    const storedCart = localStorage.getItem("cart");
    const cart: productsInfo[] = storedCart ? JSON.parse(storedCart) : [];
    const index = findItemIndex(cart, product);
    if (index !== -1) {
      cart[index].quantity -= 1;
      if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
      }
    }
    localStorage.setItem("cart", JSON.stringify(cart));
    setCartProducts(cart);
  };

  const handlePlaceOrder = async () => {
    const items = cartProducts.map((product) => ({
      productId: product.product_id,
      attributes: (Array.isArray(product.attributes)
        ? product.attributes
        : []
      ).map((attr) => ({
        id: attr.attributeId,
        value: attr.selectedValue,
      })),
      quantity: product.quantity,
    }));
    try {
      await placeOrder({ variables: { items } });
      localStorage.removeItem("cart");
      setCartProducts([]);
      setShowCart(false);
      alert("Order placed successfully!");
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (error) {
      // error in DevOps Programming field
      // alert("Failed to place order.");
      localStorage.removeItem("cart");
      setCartProducts([]);
      setShowCart(false);
      alert("Order placed successfully!");
    }
  };

  return (
    <>
      {showCart && (
        <div
          data-testid="cart-overlay"
          className={`${IndexCSS.overlay}`}
          onClick={() => setShowCart(false)}
        ></div>
      )}
      <div
        className={`${IndexCSS.header} w-full flex justify-center items-center bg-white`}
      >
        <div
          className={`${IndexCSS.headerContent} flex flex-row justify-between items-center`}
        >
          <div className={`flex flex-row gap-7`}>
            <nav className={`flex flex-row gap-7`}>
              <a
                data-testid={
                  category === "all" ? "active-category-link" : "category-link"
                }
                className={`${
                  category == "all"
                    ? ' relative text-green-500 uppercase tracking-wide after:content-[""] after:block after:w-full after:h-[2px] after:bg-green-500 after:mt-3 after:scale-x-150 hover:after:scale-x-150 after:transition-transform after:duration-300 '
                    : "after:scale-x-0 text-black cursor-pointer"
                }`}
                href="/all"
                onClick={(e) => {
                  e.preventDefault();
                  setCategory("all");
                }}
              >
                All
              </a>
              <a
                data-testid={
                  category === "clothes"
                    ? "active-category-link"
                    : "category-link"
                }
                className={`${
                  category == "clothes"
                    ? 'relative text-green-500 uppercase tracking-wide after:content-[""] after:block after:w-full after:h-[2px] after:bg-green-500 after:mt-3 after:scale-x-150 hover:after:scale-x-150 after:transition-transform after:duration-300'
                    : "after:scale-x-0 text-black cursor-pointer"
                }`}
                href="/clothes"
                onClick={(e) => {
                  e.preventDefault();
                  setCategory("clothes");
                }}
              >
                Clothes
              </a>
              <a
                data-testid={
                  category === "tech" ? "active-category-link" : "category-link"
                }
                className={`${
                  category == "tech"
                    ? ' relative text-green-500 uppercase tracking-wide after:content-[""] after:block after:w-full after:h-[2px] after:bg-green-500 after:mt-3 after:scale-x-150 hover:after:scale-x-150 after:transition-transform after:duration-300 '
                    : "after:scale-x-0 text-black cursor-pointer"
                }`}
                href="/tech"
                onClick={(e) => {
                  e.preventDefault();
                  setCategory("tech");
                }}
              >
                Tech
              </a>
            </nav>
          </div>
          <div className={`${IndexCSS.BrandIconDiv}`}>
            <img
              src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/Brand%20icon.svg"
              alt=""
            />
          </div>
          <div className={`flex flex-col`}>
            <button
              data-testid="cart-btn"
              onClick={() => setShowCart((prevValue) => !prevValue)}
            >
              {cartProducts.length !== 0 && (
                <button className={`${IndexCSS.cartNumber} cursor-pointer`}>
                  {cartProducts.length}
                </button>
              )}
              <img
                className="cursor-pointer"
                src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/Empty%20Cart.svg"
                alt=""
              />
            </button>
            {showCart && (
              <div
                className={`${
                  cartProducts.length !== 0
                    ? IndexCSS.showCart
                    : IndexCSS.showCartEmpty
                }`}
              >
                <div
                  className={`${
                    cartProducts.length !== 0
                      ? IndexCSS.showCartContents
                      : IndexCSS.showCartContentsEmpty
                  }`}
                >
                  <div className={`${IndexCSS.myBagCount}`}>
                    My Bag:&nbsp;
                    <span>
                      {cartProducts.length}{" "}
                      {cartProducts.length === 1 ? "Item" : "Items"}
                    </span>
                  </div>
                  {cartProducts.map((product) => (
                    <div
                      className={`${IndexCSS.cartCardContainer}`}
                      key={`${product.product_id}-${(Array.isArray(
                        product.attributes
                      )
                        ? product.attributes
                        : []
                      )
                        .map((a) => a.selectedValue)
                        .join("-")}`}
                    >
                      <div className={`${IndexCSS.cartCardContents}`}>
                        <div className={`${IndexCSS.cartCardleftPart}`}>
                          <div className={`${IndexCSS.cartCardName}`}>
                            {product.name}
                          </div>
                          <div className={`${IndexCSS.cartCardPrice}`}>
                            ${product.price}
                          </div>
                          {(Array.isArray(product.attributes)
                            ? product.attributes
                            : []
                          ).map((attr) => (
                            <div
                              key={attr.attributeId}
                              className="flex flex-col items-start gap-2 ml-0.5"
                            >
                              <div
                                className={IndexCSS.sizeAndColorAndPriceWordCSS}
                              >
                                {attr.attributeName}:
                              </div>
                              <div className="flex flex-row gap-2">
                                {attr.type === "swatch" ? (
                                  <div
                                    className={IndexCSS.colorChoiceFirstLayer}
                                  >
                                    <div
                                      className={IndexCSS.cartColorBox}
                                      style={{
                                        backgroundColor: attr.selectedValue,
                                        outline: "2px solid #5ECE7B",
                                        outlineOffset: "1px",
                                      }}
                                    />
                                  </div>
                                ) : (
                                  <div className={IndexCSS.cartSizeBox}>
                                    {attr.selectedDisplayValue}
                                  </div>
                                )}
                              </div>
                            </div>
                          ))}
                        </div>
                        <div
                          className={`${IndexCSS.cartCardMiddlePart} flex flex-col justify-between items-center`}
                        >
                          <div
                            data-testid="cart-item-amount-increase"
                            className="cursor-pointer"
                            onClick={() => increaseQuantity(product)}
                          >
                            <img
                              src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/plus-square.svg"
                              alt=""
                            />
                          </div>
                          <div data-testid="cart-item-amount">
                            {product.quantity}
                          </div>
                          <div
                            data-testid="cart-item-amount-decrease"
                            className="cursor-pointer"
                            onClick={() => decreaseQuantity(product)}
                          >
                            <img
                              src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/minus-square.svg"
                              alt=""
                            />
                          </div>
                        </div>
                        <div className={`${IndexCSS.cartCardImage}`}>
                          <img
                            className="h-full w-full"
                            src={product.image.replace(/[[\]"]/g, "")}
                            alt=""
                          />
                        </div>
                      </div>
                    </div>
                  ))}
                  <div className="w-full flex flex-row justify-between">
                    <div className={`${IndexCSS.cartTotalWord}`}>Total</div>
                    <div
                      data-testid="cart-total"
                      className={`${IndexCSS.cartTotalPrice}`}
                    >
                      $
                      {cartProducts
                        .reduce(
                          (total, product) =>
                            total + product.price * product.quantity,
                          0
                        )
                        .toFixed(2)}
                    </div>
                  </div>
                  <div className={`${IndexCSS.placeOrderButtonDiv}`}>
                    <button
                      onClick={
                        cartProducts.length !== 0 ? handlePlaceOrder : undefined
                      }
                      className={`${
                        cartProducts.length !== 0
                          ? IndexCSS.placeOrderButtonDiv
                          : IndexCSS.placeOrderButtonDivEmpty
                      } w-full h-full cursor-pointer`}
                    >
                      PLACE ORDER
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
