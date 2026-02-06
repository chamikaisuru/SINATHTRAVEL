
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/components/ui/carousel";
import { Card, CardContent } from "@/components/ui/card";
import { Star, Quote } from "lucide-react";

const testimonials = [
    {
        name: "John Doe",
        location: "United Kingdom",
        comment: "Sinath Travels made our Sri Lanka trip unforgettable! The driver was professional and the itinerary was perfect.",
        rating: 5,
    },
    {
        name: "Sarah Smith",
        location: "Australia",
        comment: "Excellent service for visa processing. Got my Dubai visa in just 2 days. Highly recommended!",
        rating: 5,
    },
    {
        name: "Michael Chen",
        location: "Singapore",
        comment: "Booked a family tour package. Everything from hotels to transport was top notch. Great value for money.",
        rating: 5,
    },
    {
        name: "Amara Perera",
        location: "Sri Lanka",
        comment: "Very helpful staff properly guided me for my Japan visa. Thank you Sinath Travels.",
        rating: 4,
    },
];

export default function Testimonials() {
    return (
        <section className="py-20 bg-muted/30">
            <div className="container mx-auto px-4">
                <div className="text-center mb-12">
                    <h2 className="text-3xl md:text-4xl font-heading font-bold text-primary mb-4">
                        What Our Clients Say
                    </h2>
                    <p className="text-muted-foreground max-w-2xl mx-auto">
                        Real experiences from travelers who trusted us with their journey.
                    </p>
                </div>

                <Carousel
                    opts={{
                        align: "start",
                        loop: true,
                    }}
                    className="w-full max-w-5xl mx-auto"
                >
                    <CarouselContent className="-ml-2 md:-ml-4">
                        {testimonials.map((t, index) => (
                            <CarouselItem key={index} className="pl-2 md:pl-4 md:basis-1/2 lg:basis-1/3">
                                <div className="p-1 h-full">
                                    <Card className="h-full border-none shadow-md hover:shadow-lg transition-shadow duration-300">
                                        <CardContent className="flex flex-col p-6 h-full">
                                            <Quote className="w-8 h-8 text-secondary/20 mb-4" />
                                            <p className="text-muted-foreground flex-grow mb-6 italic">
                                                "{t.comment}"
                                            </p>
                                            <div className="flex items-center gap-1 mb-2">
                                                {Array.from({ length: 5 }).map((_, i) => (
                                                    <Star
                                                        key={i}
                                                        className={`w-4 h-4 ${i < t.rating ? "text-accent fill-accent" : "text-muted"
                                                            }`}
                                                    />
                                                ))}
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-primary">{t.name}</h4>
                                                <span className="text-xs text-muted-foreground uppercase tracking-wide">
                                                    {t.location}
                                                </span>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </CarouselItem>
                        ))}
                    </CarouselContent>
                    <CarouselPrevious className="hidden md:flex -left-12 border-primary text-primary hover:bg-primary hover:text-white" />
                    <CarouselNext className="hidden md:flex -right-12 border-primary text-primary hover:bg-primary hover:text-white" />
                </Carousel>
            </div>
        </section>
    );
}
