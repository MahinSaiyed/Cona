import { NavLink, Route, Routes } from 'react-router-dom';

const phoneNumber = '+91 98240 26357';
const phoneLink = 'tel:+919824026357';
const whatsappLink = 'https://wa.me/919824026357';

const products = [
  {
    name: 'Safety Shoes',
    description: 'Durable and secure designs for industrial and on-site protection.',
    image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80'
  },
  {
    name: 'Formal Shoes',
    description: 'Classic formal pairs made for office comfort and premium style.',
    image: 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=900&q=80'
  },
  {
    name: 'Leather Shoes',
    description: 'High-quality leather options with long-lasting finish and fit.',
    image: 'https://images.unsplash.com/photo-1616406432452-07bc5938759d?auto=format&fit=crop&w=900&q=80'
  },
  {
    name: 'Casual Shoes',
    description: 'Everyday casual styles balancing comfort, grip, and modern look.',
    image: 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=900&q=80'
  },
  {
    name: 'Industrial Shoes',
    description: 'Heavy-duty footwear engineered for demanding working conditions.',
    image: 'https://images.unsplash.com/photo-1556048219-bb6978360b84?auto=format&fit=crop&w=900&q=80'
  }
];

const gallery = [
  'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1463100099107-aa0980c362e6?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=900&q=80'
];

const layoutNav = (
  <header>
    <div className="top-bar">
      <span>Cona Footwear • Navrangpura, Ahmedabad</span>
      <a href={phoneLink}>{phoneNumber}</a>
    </div>
    <nav className="main-nav">
      <h1>Cona Footwear</h1>
      <div>
        <NavLink to="/">Home</NavLink>
        <NavLink to="/products">Products</NavLink>
        <NavLink to="/about">About</NavLink>
        <NavLink to="/contact">Contact</NavLink>
      </div>
    </nav>
  </header>
);

function Home() {
  return (
    <>
      <section className="hero">
        <div className="overlay" />
        <div className="hero-content">
          <p className="tag">Premium Collection • Inquiry Based</p>
          <h2>Premium Footwear Store in Ahmedabad</h2>
          <p>
            Explore dependable and stylish footwear at Cona Footwear, trusted by families and industrial
            customers across Ahmedabad.
          </p>
          <div className="hero-actions">
            <NavLink to="/products" className="btn btn-gold">View Products</NavLink>
            <a href={phoneLink} className="btn btn-outline">Call Now</a>
          </div>
        </div>
      </section>

      <section className="section">
        <div className="section-header">
          <h3>Featured Products</h3>
          <p>Discover popular footwear categories available for enquiry and quick assistance.</p>
        </div>
        <div className="product-grid">
          {products.map((product) => (
            <article className="card" key={product.name}>
              <img src={product.image} alt={product.name} loading="lazy" />
              <div className="card-body">
                <h4>{product.name}</h4>
                <p>{product.description}</p>
                <a className="btn btn-dark" href={whatsappLink} target="_blank" rel="noreferrer">
                  Enquire on WhatsApp
                </a>
              </div>
            </article>
          ))}
        </div>
      </section>

      <section className="section gallery-section">
        <div className="section-header">
          <h3>Store Gallery</h3>
          <p>Take a look at shoe collections and style inspiration from Cona Footwear.</p>
        </div>
        <div className="gallery-grid">
          {gallery.map((image, i) => (
            <img key={image} src={image} alt={`Cona Footwear gallery ${i + 1}`} loading="lazy" />
          ))}
        </div>
      </section>
    </>
  );
}

function Products() {
  return (
    <section className="section page-section">
      <div className="section-header">
        <h2>Products</h2>
        <p>Inquiry-first shopping experience for all footwear needs.</p>
      </div>
      <div className="product-grid">
        {products.map((product) => (
          <article className="card" key={product.name}>
            <img src={product.image} alt={product.name} loading="lazy" />
            <div className="card-body">
              <h4>{product.name}</h4>
              <p>{product.description}</p>
              <a className="btn btn-dark" href={whatsappLink} target="_blank" rel="noreferrer">
                Enquire on WhatsApp
              </a>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}

function About() {
  return (
    <section className="section page-section about-card">
      <h2>About Cona Footwear</h2>
      <p>
        Cona Footwear is a trusted footwear shop in Navrangpura, Ahmedabad offering safety shoes, leather
        shoes, and quality footwear for daily and industrial use.
      </p>
      <p>
        We focus on personal service, right fit guidance, and reliable footwear options for working
        professionals, students, and families.
      </p>
    </section>
  );
}

function Contact() {
  return (
    <section className="section page-section contact-grid">
      <div className="contact-card">
        <h2>Contact Us</h2>
        <p><strong>Phone:</strong> <a href={phoneLink}>{phoneNumber}</a></p>
        <p><strong>Location:</strong> Navrangpura, Ahmedabad, Gujarat, India</p>
        <div className="hero-actions">
          <a href={phoneLink} className="btn btn-gold">Call Now</a>
          <a href={whatsappLink} className="btn btn-dark" target="_blank" rel="noreferrer">WhatsApp</a>
        </div>
      </div>
      <iframe
        title="Cona Footwear Map"
        loading="lazy"
        src="https://www.google.com/maps?q=Navrangpura,Ahmedabad&output=embed"
      />
    </section>
  );
}

export default function App() {
  return (
    <div className="app">
      {layoutNav}
      <main>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/products" element={<Products />} />
          <Route path="/about" element={<About />} />
          <Route path="/contact" element={<Contact />} />
        </Routes>
      </main>
      <footer>
        <p>Cona Footwear, Navrangpura, Ahmedabad, Gujarat, India</p>
        <p><a href={phoneLink}>{phoneNumber}</a> • Serving Ahmedabad customers</p>
      </footer>
    </div>
  );
}
