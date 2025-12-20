import { createContext, useContext, useState, ReactNode } from "react";

type Language = "en" | "si" | "ta";

interface Translations {
  nav: {
    home: string;
    about: string;
    services: string;
    contact: string;
    bookNow: string;
  };
  hero: {
    title: string;
    subtitle: string;
    cta: string;
  };
  about: {
    title: string;
    content: string;
    iata: string;
  };
  services: {
    title: string;
    airTicketing: string;
    visaServices: string;
    tourPackages: string;
    desc: string;
  };
  contact: {
    title: string;
    address: string;
    phone: string;
    email: string;
    message: string;
    send: string;
  };
  footer: {
    rights: string;
  };
}

const translations: Record<Language, Translations> = {
  en: {
    nav: {
      home: "Home",
      about: "About Us",
      services: "Services",
      contact: "Contact",
      bookNow: "Book Now",
    },
    hero: {
      title: "Explore the World with Sinath Travels",
      subtitle: "Your trusted IATA accredited partner for unforgettable journeys.",
      cta: "Explore Packages",
    },
    about: {
      title: "About Sinath Travels",
      content: "Sinath Travels and Tours is a premier travel agency dedicated to providing exceptional travel experiences. With our IATA accreditation and years of expertise, we ensure your journey is smooth, safe, and memorable.",
      iata: "IATA Accredited Agent",
    },
    services: {
      title: "Our Services",
      airTicketing: "Air Ticketing",
      visaServices: "Visa Services",
      tourPackages: "Tour Packages",
      desc: "Comprehensive travel solutions tailored to your needs.",
    },
    contact: {
      title: "Contact Us",
      address: "Address",
      phone: "Phone",
      email: "Email",
      message: "Message",
      send: "Send Inquiry",
    },
    footer: {
      rights: "All rights reserved.",
    },
  },
  si: {
    nav: {
      home: "මුල් පිටුව",
      about: "අපි ගැන",
      services: "සේවා",
      contact: "අමතන්න",
      bookNow: "වෙන්කරවා ගන්න",
    },
    hero: {
      title: "සිනාත් ට්‍රැවල්ස් සමඟ ලෝකය දකින්න",
      subtitle: "ඔබගේ විශ්වාසනීය IATA පිළිගත් සංචාරක සහකරු.",
      cta: "පැකේජ බලන්න",
    },
    about: {
      title: "සිනාත් ට්‍රැවල්ස් ගැන",
      content: "සිනාත් ට්‍රැවල්ස් ඇන්ඩ් ටුවර්ස් යනු සුවිශේෂී සංචාරක අත්දැකීම් ලබා දීමට කැපවී සිටින ප්‍රමුඛ පෙළේ සංචාරක ආයතනයකි.",
      iata: "IATA පිළිගත් නියෝජිත",
    },
    services: {
      title: "අපගේ සේවාවන්",
      airTicketing: "ගුවන් ටිකට්පත්",
      visaServices: "වීසා සේවා",
      tourPackages: "සංචාරක පැකේජ",
      desc: "ඔබගේ අවශ්යතා සදහා සකස් කරන ලද පුළුල් සංචාරක විසඳුම්.",
    },
    contact: {
      title: "අප අමතන්න",
      address: "ලිපිනය",
      phone: "දුරකථන",
      email: "විද්‍යුත් තැපෑල",
      message: "පණිවිඩය",
      send: "යවන්න",
    },
    footer: {
      rights: "සියලුම හිමිකම් ඇවිරිණි.",
    },
  },
  ta: {
    nav: {
      home: "முகப்பு",
      about: "எங்களைப் பற்றி",
      services: "சேவைகள்",
      contact: "தொடர்பு",
      bookNow: "முன்பதிவு",
    },
    hero: {
      title: "சினாத் டிராவல்ஸ் உடன் உலகை ஆராயுங்கள்",
      subtitle: "உங்கள் நம்பகமான IATA அங்கீகாரம் பெற்ற பயண பங்குதாரர்.",
      cta: "தொகுப்புகளை ஆராய்க",
    },
    about: {
      title: "சினாத் டிராவல்ஸ் பற்றி",
      content: "சினாத் டிராவல்ஸ் அண்ட் டூர்ஸ் ஒரு முதன்மையான பயண முகவர் நிறுவனமாகும்.",
      iata: "IATA அங்கீகாரம் பெற்ற முகவர்",
    },
    services: {
      title: "எங்கள் சேவைகள்",
      airTicketing: "விமான டிக்கெட்",
      visaServices: "விசா சேவைகள்",
      tourPackages: "சுற்றுலா தொகுப்புகள்",
      desc: "உங்கள் தேவைகளுக்கு ஏற்ப விரிவான பயண தீர்வுகள்.",
    },
    contact: {
      title: "தொடர்பு கொள்ள",
      address: "முகவரி",
      phone: "தொலைபேசி",
      email: "மின்னஞ்சல்",
      message: "செய்தழி",
      send: "அனுப்புக",
    },
    footer: {
      rights: "அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.",
    },
  },
};

type LanguageContextType = {
  language: Language;
  setLanguage: (lang: Language) => void;
  t: Translations;
};

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [language, setLanguage] = useState<Language>("en");

  return (
    <LanguageContext.Provider value={{ language, setLanguage, t: translations[language] }}>
      {children}
    </LanguageContext.Provider>
  );
}

export function useLanguage() {
  const context = useContext(LanguageContext);
  if (context === undefined) {
    throw new Error("useLanguage must be used within a LanguageProvider");
  }
  return context;
}
