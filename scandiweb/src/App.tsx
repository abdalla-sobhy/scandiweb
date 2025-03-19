import './App.css'
import { BrowserRouter as Router, Route, Routes, Navigate } from "react-router-dom";
import Index from './pages/Index.tsx'
import ProductPage from './pages/ProductPage.tsx';
import { ProductProvider } from "./components/ProductContext";


function App() {

  return (
    <>
      <Router>
          <ProductProvider>
            <Routes>
              <Route path="/" element={<Navigate to="/Index" />} />
              <Route path="/Index" element={<Index />} />
              <Route path="/ProductPage" element={<ProductPage />} />
            </Routes>
          </ProductProvider>
      </Router>
    </>
  )
}

export default App
