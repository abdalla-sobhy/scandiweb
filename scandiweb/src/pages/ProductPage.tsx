import { useState } from "react";
import { useQuery } from "@apollo/client";
import { GET_PRODUCT_BY_ID } from "../queries";
import Header from "../components/Header";
import productPageCSS from "../../public/styles/ProductPage.module.css";
// import { useProduct } from "../components/ProductContext";
import { useNavigate } from "react-router-dom";
import { useSearchParams } from "react-router-dom";
import parse from "html-react-parser";

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
interface Product {
  id: string;
  name: string;
  prices: Price[];
  gallery: string[];
  category: Category;
  description: string;
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
export default function ProductPage() {
  const [searchParams] = useSearchParams();
  const productId = searchParams.get('id');
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [toggleWheneAddToCart, setToggleWheneAddToCart] =
    useState<boolean>(false);
  const [itemAdded, setItemAdded] = useState<boolean>(false);
  const categoryFromQuery = searchParams.get("category") as Category;
  const navigateTo = useNavigate();
  const { data, loading, error } = useQuery(GET_PRODUCT_BY_ID, {
    variables: { id: productId },
    skip: !productId
  });
  const [selectedAttributes, setSelectedAttributes] = useState<
    Record<string, string>
  >({});
  const product = data?.product as Product;
  const images = product?.gallery || [];
  const priceData = product?.prices?.[0];
  const handleNextImage = () => {
    setCurrentImageIndex((prevIndex) => (prevIndex + 1) % images.length);
  };
  const handlePrevImage = () => {
    setCurrentImageIndex(
      (prevIndex) => (prevIndex - 1 + images.length) % images.length
    );
  };
  const handleAttributeSelect = (attributeId: string, itemId: string) => {
    setSelectedAttributes((prev) => ({ ...prev, [attributeId]: itemId }));
  };
  const handleAddToCart = () => {
    if (!product) return;
    const missingAttributes = product.attributes
      .filter((attr) => !selectedAttributes[attr.id])
      .map((attr) => attr.name);
    if (missingAttributes.length > 0) {
      alert(`Please select: ${missingAttributes.join(", ")}`);
      return;
    }
    const attributeSelections = product.attributes.map((attr) => {
      const selectedItemId = selectedAttributes[attr.id];
      const selectedItem = attr.items.find(
        (item) => item.id === selectedItemId
      );
      return {
        attributeId: attr.id,
        attributeName: attr.name,
        type: attr.type,
        selectedValue: selectedItem?.value || "",
        selectedDisplayValue: selectedItem?.displayValue || "",
      };
    });
    const orderItem: OrderItem = {
      product_id: product.id,
      name: product.name,
      price: priceData?.amount || 0,
      currency: priceData?.currency.label || "USD",
      image: product.gallery[0],
      attributes: attributeSelections,
      category: product.category,
      quantity: 1,
    };
    const storedCart = localStorage.getItem("cart");
    const cart: OrderItem[] = storedCart ? JSON.parse(storedCart) : [];
    const existingIndex = cart.findIndex(
      (item) =>
        item.product_id === orderItem.product_id &&
        JSON.stringify(item.attributes) === JSON.stringify(orderItem.attributes)
    );

    if (existingIndex !== -1) {
      cart[existingIndex].quantity += 1;
    } else {
      cart.push(orderItem);
    }
    localStorage.setItem("cart", JSON.stringify(cart));
    setToggleWheneAddToCart((prev) => !prev);
    setItemAdded(true);
  };
  const handleSetCategory = (newCategory: Category) => {
    navigateTo(`/Index?category=${newCategory}`);
  };

  if (loading)
    return (
      <div className={`${productPageCSS.loader}`}>
        <div className={`${productPageCSS.item1}`}></div>
        <div className={`${productPageCSS.item2}`}></div>
        <div className={`${productPageCSS.item3}`}></div>
      </div>
    );
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div className="h-full w-full flex-col flex gap-12 text-black bg-white">
      <Header
        category={categoryFromQuery}
        setCategory={handleSetCategory}
        toggleWheneAddToCart={toggleWheneAddToCart}
        itemAdded={itemAdded}
      />
      <div className="w-full justify-center mt-8">
        <div className={`${productPageCSS.pageContent}`}>
          <div
            data-testid="product-gallery"
            className={`${productPageCSS.leftPart} flex flex-col gap-4`}
          >
            {images.map((img: string, index: number) => (
              <div
                key={index}
                onClick={() => setCurrentImageIndex(index)}
                className="cursor-pointer"
              >
                <img src={img} alt={`Thumbnail ${index}`} />
              </div>
            ))}
          </div>
          <div className={`${productPageCSS.mainImage}`}>
            <div className={`${productPageCSS.leftRightArrwosDiv}`}>
              <div className="cursor-pointer">
                <img
                  src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/Group%201417.svg"
                  onClick={handlePrevImage}
                  alt="Left Arrow"
                />
              </div>
              <div className="cursor-pointer">
                <img
                  src="https://raw.githubusercontent.com/abdalla-sobhy/scandiwebfrontend/refs/heads/master/src/assets/Group%201420.svg"
                  onClick={handleNextImage}
                  alt="Right Arrow"
                />
              </div>
            </div>
            <img
              className="w-full h-full"
              src={images[currentImageIndex]}
              alt=""
            />
          </div>
          <div className={`${productPageCSS.rightPart}`}>
            <div
              className={`${productPageCSS.rightPartContents} flex flex-col gap-8 ml-12`}
            >
              <div className={`${productPageCSS.productName} flex`}>
                {product?.name}
              </div>
              {product?.attributes?.map((attributeSet) => {
                return (
                  <div
                    key={attributeSet.id}
                    className="flex flex-col items-start gap-2"
                  >
                    <div
                      className={`${productPageCSS.sizeAndColorAndPriceWordCSS}`}
                    >
                      {attributeSet.name.toUpperCase()}:
                    </div>
                    <div className="flex flex-row gap-3">
                      {attributeSet.items.map((item) =>
                        attributeSet.type === "swatch" ? (
                          <div
                            key={item.id}
                            className={`${productPageCSS.colorBox} cursor-pointer`}
                            style={{
                              backgroundColor: item.value,
                              outline:
                                selectedAttributes[attributeSet.id] === item.id
                                  ? "2px solid #5ECE7B"
                                  : "none",
                              outlineOffset: "1px",
                            }}
                            onClick={() =>
                              handleAttributeSelect(attributeSet.id, item.id)
                            }
                            title={item.displayValue}
                            data-testid={`product-attribute-color-${item.displayValue}`}
                          />
                        ) : (
                          <div
                            key={item.id}
                            className={`${
                              productPageCSS.sizeBox
                            } cursor-pointer ${
                              selectedAttributes[attributeSet.id] === item.id
                                ? "bg-[#2B2B2B] text-white"
                                : ""
                            }`}
                            onClick={() =>
                              handleAttributeSelect(attributeSet.id, item.id)
                            }
                            data-testid={`product-attribute-capacity-${item.displayValue}`}
                          >
                            {item.displayValue}
                          </div>
                        )
                      )}
                    </div>
                  </div>
                );
              })}

              <div className="flex flex-col items-start gap-4">
                <div
                  className={`${productPageCSS.sizeAndColorAndPriceWordCSS}`}
                >
                  PRICE:
                </div>
                <div
                  className={`${productPageCSS.sizeAndColorAndPriceWordCSS}`}
                >
                  {product?.prices[0]?.currency.symbol}
                  {product?.prices[0]?.amount.toFixed(2)}
                </div>
              </div>

              <div
                className={`${productPageCSS.addToCartButtonDiv} ${
                  product?.inStock ? "bg-[#5ECE7B]" : "bg-gray-300"
                } ${!product?.inStock ? "disabled" : ""}`}
              >
                <button
                  data-testid="add-to-cart"
                  className={`w-full h-full ${
                    product?.inStock ? "cursor-pointer" : ""
                  }`}
                  onClick={product?.inStock ? handleAddToCart : undefined}
                  disabled={!product?.inStock}
                >
                  ADD TO CART
                </button>
              </div>

              <div
                data-testid="product-description"
                className={`${productPageCSS.underButtonText}`}
              >
                {parse(product?.description || "")}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
