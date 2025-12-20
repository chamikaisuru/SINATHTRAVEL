import heroImg from "@assets/stock_images/travel_agency_hero_i_2c89a1ec.jpg";
import sigiriyaImg from "@assets/stock_images/sri_lanka_sigiriya_r_ddcb0491.jpg";
import dubaiImg from "@assets/stock_images/dubai_skyline_with_b_8fae68a6.jpg";
import maldivesImg from "@assets/stock_images/maldives_water_villa_e130bb66.jpg";
import visaImg from "@assets/stock_images/passport_and_visa_ap_e2db6da8.jpg";
import FreeVisaServicesimg from "@assets/stock_images/Free_Visa_Services.jpg";
import touristsImg from "@assets/stock_images/happy_tourists_group_d3b2c5f2.jpg";

export const images = {
  hero: heroImg,
  sigiriya: sigiriyaImg,
  dubai: dubaiImg,
  maldives: maldivesImg,
  visa: visaImg,
  FreeVisaServicesimg: FreeVisaServicesimg,
  tourists: touristsImg,
};

export const packages = [
  {
    id: 1,
    title: "Sri Lanka Heritage Tour",
    description: "Explore the ancient wonders of Sigiriya, Polonnaruwa, and Kandy.",
    image: sigiriyaImg,
    price: "$500",
    duration: "5 Days",
  },
  {
    id: 2,
    title: "Dubai Shopping Festival",
    description: "Experience the luxury and shopping extravaganza of Dubai.",
    image: dubaiImg,
    price: "$800",
    duration: "4 Days",
  },
  {
    id: 3,
    title: "Maldives Luxury Escape",
    description: "Relax in overwater villas with crystal clear waters.",
    image: maldivesImg,
    price: "$1200",
    duration: "3 Days",
  },
];

export const services = [
  {
    id: 1,
    title: "Air Ticketing",
    description: "Best rates for all international and domestic flights. We handle all airline reservations.",
    icon: "Plane",
  },
  {
    id: 2,
    title: "Visa Services",
    description: "Hassle-free visa assistance for Dubai, Singapore, Malaysia, and more.",
    icon: "FileCheck",
  },
  {
    id: 3,
    title: "Tour Packages",
    description: "Customized holiday packages for families, couples, and groups.",
    icon: "Map",
  },
];
